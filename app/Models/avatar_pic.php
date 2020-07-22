<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class avatar_pic extends Model
{
    protected $table    = 'avatar_pic';
    protected $fillable = [
       	'id', 'pic'
    ];
    public $timestamps  = false;

    public function scopeInsertPic($query, $pic)
    {
        return $query->insertGetId(['pic' => $pic]);
    }

    public function scopeGetPic($query, $id)
    {
    	$result = $query->where('id', $id)->get();
    	if (!$result->isEmpty()) {
    		return $result->first()->pic;
    	} else {
    		return '';
    	}
    }

}
