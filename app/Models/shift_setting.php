<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shift_setting extends Model
{
    protected $table    = 'shift_setting';
    protected $fillable = [
       	'id', 'title', 'starttime', 'endtime', 'status'
    ];
    public $timestamps  = false;

}
