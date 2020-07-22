<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class account_remark extends Model
{
    protected $table    = 'account_remark';
    protected $fillable = [
       	'id', 'accountid', 'type', 'remark', 'date'
    ];
    public $timestamps  = false;

}
