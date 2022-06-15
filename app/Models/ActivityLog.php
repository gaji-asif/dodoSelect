<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo(User::class)->withDefault(['name' => '']);
    }

    public function product(){
        return $this->belongsTo(Product::class)->withDefault(['product_name' => '']);
    }

    public function orderPurchase(){
        return $this->belongsTo(OrderPurchase::class);
    }

    /**
     * Query to search from table
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string|null $keyword
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeSearchActivityLogTable($query, $keyword = null)
    {
        if (!empty($keyword)) {
            return $query->orWhereHas('user', function(Builder $user) use ($keyword) {
                $user->searchByName($keyword);
            })
                ->orWhere(function(Builder $activity_log) use ($keyword) {
                    $activity_log->where('created_at', 'like', "%$keyword%")
                        ->orWhere('quantity', 'like', "%$keyword%")
                        ->orWhere('action', 'like', "%$keyword%")
                        ->orWhere('product_name', 'like', "%$keyword%")
                        ->orWhere('product_code', 'like', "%$keyword%");
                });
        }
        return;
    }

    /**
     * SubQuery to make `user_name` as column
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeUserNameAsColumn($query)
    {
        return $query->addSelect(['user_name' => User::select('name')
            ->whereColumn('id', 'activity_logs.user_id')
            ->limit(1)
        ]);
    }

    /**
     * Product updates activity log
     *
     * @param  string  $action
     * @param  int  $productId
     * @param  int|null  $quantity
     */
    public static function updateProductActivityLog($action, $productId)
    {
        $activityLog = new ActivityLog;

        $userRole = Auth::user()->role ?? '';
        $userId = Auth::user()->id ?? 0;

        $activityLog->user_id = $userId;
        if ($userRole == User::ROLE_STAFF) {
            $activityLog->user_id = Auth::user()->staff_id;
        }

        $activityLog->product_id = $productId;

        $product = Product::find($productId);
        $activityLog->product_name = $product->product_name ?? '';
        $activityLog->product_code = $product->product_code ?? '';

        $activityLog->action = $action;
        $activityLog->save();
    }

    /**
     * Update stock activity log
     *
     * @param  string  $active
     * @param  int  $stockLogId
     * @return void
     */
    public static function updateStockActivityLog($action, $stockLogId, $quantity = null, $product_id = null)
    {
        $activityLog = new ActivityLog;

        $userRole = Auth::user()->role ?? '';
        $userId = Auth::user()->id ?? 0;

        $activityLog->user_id = $userId;
        if ($userRole == User::ROLE_STAFF) {
            $activityLog->user_id = Auth::user()->staff_id;
        }

        $activityLog->stock_id = $stockLogId;

        $stockLog = StockLog::query()
            ->where('id', $stockLogId)
            ->with('product')
            ->first();

        $activityLog->product_id = $stockLog->product_id ?? 0;
        $activityLog->product_name = $stockLog->product->product_name ?? '';
        $activityLog->product_code = $stockLog->product->product_code ?? '';

        $activityLog->quantity = $quantity;
        $activityLog->action = $action;

        $activityLog->save();
    }

    /**
     * Undo activity logs data
     *
     * @param  int  $logId
     * @return void
     */
    public static function undoActivityLog($logId){
        $activityLog = ActivityLog::find($logId);

        ActivityLog::destroy($activityLog->id);

        $action = $activityLog->action;

        switch ($action){
            case 'Create new product':
                Product::destroy($activityLog->product_id);
                $permission = Permission::where('product_id', '=', $activityLog->product_id)->first();
                if ($permission){
                    $permission->delete();
                }
                break;

            case 'Delete product':
            case 'Delete bulk product':
                Product::withTrashed()->find($activityLog->product_id)->restore();
                $product = Product::findOrFail($activityLog->product_id);
                Permission::create([
                    'name' => $product->product_code,
                    'product_id' => $product->id,
                    'user_type' => 1
                ]);
                break;

            case 'Add product':
            case 'Remove product':
                StockLog::destroy($activityLog->stock_id);
                ActivityLog::destroy($activityLog->id);
                break;

            case 'Update added product':
                $stockLog = StockLog::where('id', $activityLog->stock_id)->first();
                $stockLog->quantity -= $activityLog->quantity;
                $stockLog->save();

                $log = ActivityLog::latest('id')->first();
                $log->delete();
                break;

            case 'Update removed product':
                $stockLog = StockLog::where('id', $activityLog->stock_id)->first();
                $stockLog->quantity += $activityLog->quantity;
                $stockLog->save();

                $log = ActivityLog::latest('id')->first();
                $log->delete();
                break;

            case 'Delete stock log':
            case 'Bulk delete stock log':
                StockLog::withTrashed()->find($activityLog->stock_id)->restore();

                $log = ActivityLog::latest('id')->first();
                $log->delete();
                break;

        }
    }
}
