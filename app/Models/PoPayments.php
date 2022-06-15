<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PhpMyAdmin\SqlParser\Utils\Table;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoPayments extends Model
{
    use HasFactory;

    protected $table = 'po_payments';
    
    /**
     * Get all Payment Info
     *
     * @return array
     */
    public static function getAllPaymentByPOID($id)
    {
    return DB::table('po_payments')
              ->where('order_purchase_id', $id)->first();                    
 
    }




}
