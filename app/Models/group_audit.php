<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class group_audit extends Model
{
    protected $table    = 'group_audit';
    protected $fillable = [
       	'id', 'mapping', 'method', 'para', 'updateuser', 'date'
    ];
    public $timestamps  = false;

}
