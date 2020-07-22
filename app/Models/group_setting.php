<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class group_setting extends Model
{
    protected $table    = 'group_setting';
    protected $fillable = [
       	'id', 'title', 'parent', 'level', 'status'
    ];
    public $timestamps  = false;
    public function scopeGetAllGroup($q)
    {
        $res = $q->where('level', '0')->get();
        return $res;
    }

    public function scopeGetAllClass($q)
    {
        $res = $q->where('level', '1')->get();
        return $res;
    }

    public function scopeGetGroupById($q, $id)
    {
        $res = $q->where('id', $id)->get();
        return $res;
    }
}
