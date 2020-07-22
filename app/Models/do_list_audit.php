<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class do_list_audit extends Model
{
    protected $table    = 'do_list_audit';
    protected $fillable = [
       	'id', 'mapping', 'method', 'para', 'updateuser', 'date'
    ];
    public $timestamps  = false;

}
