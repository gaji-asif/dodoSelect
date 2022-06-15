<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory;

    /**
     * Relationship to `products` table
     *
     * @return mixed
     */
    public function products(){
        return $this->hasMany(Product::class, 'supplier_id', 'id');
    }

    /**
     * Relationship to `supplier_contacts` table
     *
     * @return mixed
     */
    public function supplierContacts(){
        return $this->hasMany(SupplierContact::class, 'supplier_id', 'id');
    }


    /**
     * Get All `supplier` By Seller ID
     * param int 
     * @return mixed
     */
    public static function getSuppliersBySellerID($sellerId){
        return DB::table('suppliers')->where('seller_id', $sellerId)->get();
   }

   /**
     * Get Default `suppliers`
     * param int 
     * @return mixed
     */
    public static function getDefaultSuppliers(){
        $sql = "SELECT DISTINCT supplier_id,suppliers.supplier_name 
                FROM `product_costs` 
                LEFT JOIN suppliers ON suppliers.id=product_costs.supplier_id 
                WHERE default_supplier=1 ORDER BY supplier_name
                ";
                
        return DB::select(DB::raw("$sql"));
   }

}
