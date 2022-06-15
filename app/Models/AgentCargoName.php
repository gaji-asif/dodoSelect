<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCargoName extends Model
{
    use HasFactory;

    protected $table = 'agent_cargo_name';
    /**
     * Mass fillable fields
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

  
}
