<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class account extends Model
{
    protected $table    = 'account';
    protected $fillable = [
        'accountid', 'accountcode', 'nickname', 'password', 'status', 'lastlogin', 'cdate', 'level', 'group', 'shift', 'check_report', 'class'
    ];
    public $timestamps  = false;

    const OPEN = '1';
    public function scopeGetAllAccount($query, $status)
    {
        return $query->where('status', $status)->get();
    }

    public function scopeCheckAccount($query, $account)
    {
        return $query->where('accountcode', $account)->get();
    }

    public function scopeGetTodayNotCheck($query, $status, $today, $group)
    {
        $result = [];
        $result = $query->select('accountid', 'accountcode', 'avatar')
            ->where('status', $status)
            ->when($group, function ($q) use ($group) {
                $q->where('group', $group);
            })
            ->whereNotIn('accountid', function ($q) use ($status, $today) {
                $q->select('accountid')->from('account_record')
                  ->where('date', 'LIKE', "{$today}%")->where('type', '1')->where('status', $status)
                  ->distinct();
            })->get();
        return $result;
    }

    public function scopeLogin($query, $accountcode)
    {
        $result = $query->where('accountcode', $accountcode)->get();
        return $result;
    }

    public function scopeGetGroupAccount($query, $group, $status)
    {
        return $query->when($group, function($q) use ($group) {
            $q->where('group', $group);
        })->where('status', $status)->get();
    }

    public function scopeGetAccountAvatar($query, $accountid)
    {
        $avatars = [];
        $accountAvatar = $query->select('accountid', 'avatar')->whereIn('accountid', $accountid)->get();
        if (!$accountAvatar->isEmpty()) {
            foreach ($accountAvatar as $key => $value) {
                $avatars[$value['accountid']] = $value['avatar'];
            }
        }
        return $avatars;
    }

    public function scopeGetAccount($query, $accountid)
    {
        $result = $query->select('accountcode')->where('accountid', $accountid)->get();
        if ($result->isEmpty()) {
            return '';
        } else {
            return $result->first()->accountcode;
        }
    }
}
