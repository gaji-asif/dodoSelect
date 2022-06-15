<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AgentCargoMark extends Model
{
    use HasFactory;

    protected $table = 'agent_cargo_mark';
    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    // GET Table data by Column
    public static function getTableDataByColumnValue($col,$val){
        return DB::table('agent_cargo_mark')->where($col, $val)->get();
   }
   

  
}
