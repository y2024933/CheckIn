<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class on_duty extends Model
{
    protected $table    = 'on_duty';
    protected $fillable = [
       	'id', 'date', 'member'
    ];
    public $timestamps  = false;

}
