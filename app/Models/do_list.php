<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class do_list extends Model
{
    protected $table    = 'do_list';
    protected $fillable = [
       	'id', 'accountid', 'content', 'date', 'status'
    ];
    public $timestamps  = false;

    public function scopeChangeStatus($query, $id, $status)
    {
        return $query->where('id', $id)->update(['status' => $status]);
    }

}
