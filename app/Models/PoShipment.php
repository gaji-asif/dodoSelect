<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoShipment extends Model
{
    use HasFactory, SoftDeletes;

     /**
     * Define `status` value field
     *
     * @var string
     */
    CONST STATUS_OPEN = 'open';
    CONST STATUS_CLOSE = 'close';
    CONST STATUS_ARRIVE = 'arrive';
    CONST STATUS_DRAFT = 'draft';

    /**
     * Attributes should cast
     *
     * @var array
     */
    protected $casts = [
        'e_d_f' => 'date',
        'e_d_t' => 'date',
        'e_a_d_f' => 'date',
        'e_a_d_t' => 'date',
        'order_date' => 'date',
        'ship_date' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date'
    ];

    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'supplier_id'
    ];

    /**
     * Append custom attributes
     *
     * @var array
     */
    protected $appends = [
        'image_url'
    ];

    /**
     * Relationship to `suppliers` table
     *
     * @return mixed
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withDefault(['supplier_name' => '']);
    }

    /**
     * Relationship to `order_purchase_details`
     * without relation to products table
     *
     * @return mixed
     */
    public function order_purchase_details()
    {
        return $this->hasMany(OrderPurchaseDetail::class, 'order_purchase_id', 'order_purchase_id');
    }

    /**
     * Relationship to `po_shipment_details`
     * without relation to products table
     *
     * @return mixed
     */
    public function po_shipment_details()
    {
        return $this->hasMany(PoShipmentDetail::class, 'po_shipment_id', 'id');
    }

    /**
     * Relationship to `products` table
     *
     * @return mixed
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id')
                ->withDefault([
                    'product_name' => '',
                    'product_code' => ''
                ])
                ->with('getQuantity')->with('getIncoming')
                ->with('productCostDetails');
    }


    public static function insertData($table,$data = []){
        DB::table($table)->insert($data);
        $id = DB::getPdo()->lastInsertId();
        return $id;
    }

    /**
     * Update PO Shipment and Details
     *
     * @param int $id
     * @param array $data
     */
    public static function updateDataPOShipment($id,$data = []){
        DB::table('po_shipments')->where('id', $id)->update($data);
    }

    /**
     * Accessor for `image_url`
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!empty($this->attributes['image']) && file_exists(public_path($this->attributes['image']))) {
            return asset($this->attributes['image']);
        }

        return asset('No-Image-Found.png');
    }

    /**
     * Query for `POShipmentTable` data
     *
     * @param  int  $sellerId
     * @param  int  $PoStatus
     * @param  array|null  $otherParams
     * @return mixed
     */
    public static function POShipmentTable($sellerId, $supplierId = 0, $PoStatus = 0, $otherParams = [])
    {
        $supplierId = isset($otherParams['supplier_id']) ? $otherParams['supplier_id'] : null;
        $order_purchase_id = isset($otherParams['order_purchase_id']) ? $otherParams['order_purchase_id'] : null;
        $keyword = isset($otherParams['search']) ? $otherParams['search'] : null;

        $offset = isset($otherParams['offset']) ? $otherParams['offset'] : 0;
        $limit = isset($otherParams['limit']) ? $otherParams['limit'] : 10;

        $orderColumn = isset($otherParams['order_column']) ? $otherParams['order_column'] : 'P.lowest_value';
        $orderDir = isset($otherParams['order_dir']) ? $otherParams['order_dir'] : 'desc';

        $searchFilter = '';
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    supplier_name LIKE '%{$keyword}%'
                    OR factory_tracking LIKE '%{$keyword}%'
                    OR cargo_ref LIKE '%{$keyword}%'
                    OR number_of_cartons LIKE '%{$keyword}%'
                    OR domestic_logistics LIKE '%{$keyword}%'
                    OR products_code LIKE '%$keyword%'
                )";
        }

        $supplierFilter = '';
        if ($supplierId > 0) {
            $supplierFilter = "AND supplier_id = '{$supplierId}'";
        }

        $statusFilter = '';
        if (!empty($PoStatus) && ($PoStatus !='all')) {
            $statusFilter = "AND status = '{$PoStatus}'";
        }

        $order_purchase_idFilter = '';
        if ($order_purchase_id > 0) {
            $order_purchase_idFilter = "AND order_purchase_id = '{$order_purchase_id}'";
        }

        return DB::select(DB::raw("SELECT * FROM (
                SELECT ps.*,
                    GROUP_CONCAT(psd.product_code SEPARATOR ', ') as products_code,
                    acm.shipping_mark, s.supplier_name
                FROM po_shipments ps
                LEFT JOIN (
                    SELECT psd.*, p.product_name, p.product_code
                    FROM po_shipment_details psd
                    LEFT JOIN products p ON p.id = psd.product_id
                ) psd ON psd.po_shipment_id = ps.id
                LEFT JOIN agent_cargo_mark acm ON acm.id = ps.shipping_mark_id
                LEFT JOIN suppliers s ON s.id = ps.supplier_id
                WHERE ps.seller_id = '{$sellerId}'
                GROUP BY ps.id
            ) tb
            WHERE 1 = 1
                {$searchFilter}
                {$supplierFilter}
                {$statusFilter}
                {$order_purchase_idFilter}
            ORDER BY {$orderColumn} {$orderDir}
            LIMIT {$limit} OFFSET {$offset}
        "));
    }

    /**
     * Query for `POShipmentTableCount` data counter
     *
     * @param  int  $sellerId
     * @param  int  $PoStatus
     * @param  array|null  $otherParams
     * @return int
     */
    public static function POShipmentTableCount($sellerId, $supplierId = 0, $PoStatus = 0, $otherParams = [])
    {
        $order_purchase_id = isset($otherParams['order_purchase_id']) ? $otherParams['order_purchase_id'] : null;
        $supplierId = isset($otherParams['supplier_id']) ? $otherParams['supplier_id'] : null;
        $keyword = isset($otherParams['search']) ? $otherParams['search'] : null;

        $searchFilter = '';
        if (!empty($keyword)) {
            $searchFilter = "AND (
                    supplier_name LIKE '%{$keyword}%'
                    OR factory_tracking LIKE '%{$keyword}%'
                    OR cargo_ref LIKE '%{$keyword}%'
                    OR number_of_cartons LIKE '%{$keyword}%'
                    OR domestic_logistics LIKE '%{$keyword}%'
                    OR products_code LIKE '%$keyword%'
                )";
        }

        $supplierFilter = '';
        if ($supplierId > 0) {
            $supplierFilter = "AND supplier_id = '{$supplierId}'";
        }

        $statusFilter = '';
        if (!empty($PoStatus) && ($PoStatus !='all')) {
            $statusFilter = "AND status = '{$PoStatus}'";
        }

        $order_purchase_idFilter = '';
        if ($order_purchase_id > 0) {
            $order_purchase_idFilter = "AND order_purchase_id = '{$order_purchase_id}'";
        }

        $resultQuery = DB::select(DB::raw("SELECT COUNT(*) AS total FROM (
                SELECT ps.*,
                    GROUP_CONCAT(psd.product_code SEPARATOR ', ') as products_code,
                    acm.shipping_mark, s.supplier_name
                FROM po_shipments ps
                LEFT JOIN (
                    SELECT psd.*, p.product_name, p.product_code
                    FROM po_shipment_details psd
                    LEFT JOIN products p ON p.id = psd.product_id
                ) psd ON psd.po_shipment_id = ps.id
                LEFT JOIN agent_cargo_mark acm ON acm.id = ps.shipping_mark_id
                LEFT JOIN suppliers s ON s.id = ps.supplier_id
                WHERE ps.seller_id = '{$sellerId}'
                GROUP BY ps.id
            ) tb
            WHERE 1 = 1
                {$searchFilter}
                {$supplierFilter}
                {$statusFilter}
                {$order_purchase_idFilter}
        "));

        return $resultQuery[0]->total ?? 0;
    }


    /**
     * Delete Exisiting data from po_shipmens by order_purchase_id
     *
     * @return
     */
    public static function deletePOShipmentByOrderPurchaseID($order_purchase_id){
         DB::table('po_shipments')->where('order_purchase_id', $order_purchase_id)->delete();
    }

    /**
     * Delete Exisiting data from po_shipment_details by id
     *
     * @return
     */
    public static function deleteByID($id){
        DB::table('po_shipments')->where('id', $id)->delete();
   }





    /**
     * Get PO Shipment Details By id
     *
     * @return Int
     */
    public static function poShipmentDetailsByID($id,$sellerId)
    {
        return PoShipment::where('id', $id)
        ->where('seller_id',$sellerId)
        ->with('supplier')
        ->with('order_purchase_details')
        ->with([ 'po_shipment_details' => function($detail) {
            $detail->with('product')
            ->with('getShipped');
        }])
        ->get()->toArray();
    }




    /**
     * Get PO Shipment Details By id
     *
     * @return Int
     */
    public static function poShipmentDetailsByOrderPurchaseID($order_purchase_id,$sellerId)
    {
        return PoShipment::where('order_purchase_id', $order_purchase_id)
        ->where('seller_id',$sellerId)
        ->with('supplier')
        ->with('order_purchase_details')
        ->with([ 'po_shipment_details' => function($detail) {
            $detail->with('product')
            ->with('getShipped');
        }])
        ->get();
    }

    /**
     * Get PO Shipment Total By Status
     *
     * @return Int
     */
    public static function poShipmentTotalCountByStatus($sellerId,$supplierId)
    {
        $poShipmentTable = (new PoShipment())->getTable();

        $supplierFilter = null;
        if($supplierId>0){
          $supplierFilter = 'AND '.$poShipmentTable.'.`supplier_id` ='.$supplierId;
        }
        return DB::select(DB::raw("
        SELECT `{$poShipmentTable}`.`status`, count(*) as total from {$poShipmentTable}
        where `{$poShipmentTable}`.`seller_id` = $sellerId
        {$supplierFilter}
        group by {$poShipmentTable}.`status`
        "));

    }



    /**
     * Update Po Shipments
     *
     * @param int old po id,new po id
     * @param null
     */
    public static function updatePoShipmentPurchaseID($old_order_purchase_id,$order_purchase_id){

        DB::table('po_shipments')->where('order_purchase_id', $old_order_purchase_id)
        ->update([
            'order_purchase_id' =>$order_purchase_id
        ]);
    }


}
