<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class account_record extends Model
{
    protected $table    = 'account_record';
    protected $fillable = [
       	'id', 'accountid', 'type', 'status', 'date', 'day', 'ip'
    ];
    public $timestamps  = false;

    public function scopeCheckIn($query, $accountid, $type, $time, $day, $ip)
    {
        return $query->create(['accountid' => $accountid, 'type' => $type, 'status' => '1', 'date' => $time, 'day' => $day, 'ip' => $ip]);
    }

    public function scopeCheckRecord($query, $accountid, $type, $date)
    {
        return $query->where('accountid', $accountid)->where('status', '1')->where('type', $type)->where('date', 'LIKE', "{$date}%")->get();
    }

    public function scopeCheckSelf($query, $accountid, $date)
    {
        return $query->selectRaw("type, DATE_FORMAT(date, '%H:%i:%s') as date")->where('status', '1')->where('accountid', $accountid)->where('date', 'LIKE', "{$date}%")->get();
    }

}
