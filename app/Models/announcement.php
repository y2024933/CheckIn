<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class announcement extends Model
{
    protected $table    = 'announcement';
    protected $fillable = [
        'id', 'channel', 'content', 'announcer', 'cdate'];
    public $timestamps  = false;
    const OPEN = '1';

    public function scopeGetGroupAnn($query, $group)
    {
        return $query->select('accountcode', 'id', 'channel', 'content', 'announcer', 'announcement.cdate')
        ->join('account', 'account.accountid', '=', 'announcement.announcer')->whereIn('channel', ['0', $group])->get();
    }
}