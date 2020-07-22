<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class account_remark_audit extends Model
{
    protected $table    = 'account_remark_audit';
    protected $fillable = [
       	'id', 'mapping', 'method', 'para', 'updateuser', 'date'
    ];
    public $timestamps  = false;

}
