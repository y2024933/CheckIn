<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shift_audit extends Model
{
    protected $table    = 'shift_audit';
    protected $fillable = [
       	'id', 'mapping', 'method', 'para', 'updateuser', 'date'
    ];
    public $timestamps  = false;

}
