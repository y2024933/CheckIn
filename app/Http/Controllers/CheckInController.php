<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\account;
use App\Models\account_record as record;
use App\Models\account_remark;
use App\Models\account_remark_audit;
use App\Models\do_list;
use App\Models\do_list_audit;
use App\Models\group_audit;
use App\Models\group_setting;
use App\Models\shift_audit;
use App\Models\on_duty;
use App\Models\shift_setting;
use App\Models\avatar_pic as avatar;
use App\Models\announcement;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use App\Http\Middleware\JwtMiddleware;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use Intervention\Image\ImageManagerStatic;
use App\Events\PodcastAnnouncement as Podcast;
use App\Events\PrivateAnnouncement as PrivateAnn;
use Log;
use DB;
use File;
use Illuminate\Support\Facades\Redis;

class CheckInController extends Controller
{
    public $account_arr = [];
    public $record      = [];
    public function createAccount(Request $request)
    {
        $data     = [];
        $account  = $request->account;
        $name     = $request->name;
        $password = $request->password;
        $level    = $request->insert_level;
        $group    = $request->insert_group;
        $class    = $request->insert_class;
        $shift    = $request->insert_shift;
        if(!isset($account) || !isset($name) || !isset($password) || !isset($level) || !isset($group) || !isset($class) || !isset($shift)) {
            return response()->json(['code' => 1, 'msg' => '栏位不可为空'], 400);
        }
        $now      = date("Y-m-d H:i:s");
        $pw       = password_hash($password, PASSWORD_DEFAULT);
        $data     = [
            'accountcode' => $account,
            'nickname'    => $name,
            'password'    => $pw,
            'status'      => '1',
            'lastlogin'   => $now,
            'level'       => $level,
            'group'       => $group,
            'class'       => $class,
            'shift'       => $shift,
            'cdate'       => $now
        ];
        $check = account::CheckAccount($account);
        if (!$check->isEmpty()) {
            return response()->json(['code' => 1, 'msg' => '帐号重复'], 400);
        }
        $result = account::create($data);
        if ($result) {
            return response()->json(['code' => 0, 'msg' => '', 'data' => '创建成功'], 200);
        }
    }

    public function login(Request $request)
    {
        $accountcode = $request->accountcode;
        $password    = $request->password;
        $getAccount  = account::Login($accountcode);
        $allgroup    = [];

        if ($getAccount->isEmpty()) {
            return response()->json(['code' => 1, 'msg' => '无此帐号', 'data' => ''], 400);
        }
        $account = $getAccount->toArray()[0];
        $check   = password_verify($password, $account['password']);
        if (!$check) {
            return response()->json(['code' => 1, 'msg' => '密码错误', 'data' => ''], 400);
        }
        if ($account['status'] !== '1') {
            return response()->json(['code' => 1, 'msg' => '帐号已被锁定', 'data' => ''], 400);
        }
        $res = group_setting::GetGroupById($account['group']);
        if(!$res->isEmpty()) {
            $account['groupName'] = $res->first()->title;
        }
        $token = $this->jwt($account);
        return response()->json(['code' => 0, 'msg' => '', 'data' => $token], 200);
    }

    public function changePassword(Request $request)
    {
        $arr = [
                    "Alex、Jasper",
                    "Max、Wendy",
                    "Paul、Chelsea",
                    "Andy、Ts",
                    "Kitty、Jason",
                    "Chris、Bob",
                    "Catch、Raven",
                ];
        for ($i=1; $i<=1; $i++) {
            foreach ($arr as $key => $val) {
                if($key == 5) {
                    
                }
                $x = $key * $i;
                $res = on_duty::create(['date' => date("Y-m-d",strtotime("+ {$x} Days")), 'member' => $val]);
            }
        }
        die;


        for ($i=0; $i<=7; $i++) {
            $x = $i % 7;
            $res = on_duty::create(['date' => date("Y-m-d",strtotime("+ {$i} Days")), 'member' => $arr[$x]]);
        }
        die;


        $opw  = $request->old_password;
        $npw  = $request->new_password;
        $npw2 = $request->new_password2;
        if(empty($opw) || empty($npw) || empty($npw2)) {
            return response()->json(['code' => 1, 'msg' => '资料不可为空', 'data' => ''], 400);
        } else if ($npw != $npw2) {
            return response()->json(['code' => 1, 'msg' => '新密码输入不相同', 'data' => ''], 400);
        }

        $res = account::where('accountid', $request->accountid)->get();
        if(!$res->isEmpty()) {
            $check = password_verify($opw, $res->first()->password);
            if (empty($check)) {
                return response()->json(['code' => 1, 'msg' => '密码错误', 'data' => ''], 400);
            }

            $check2 = account::where('accountid', $request->accountid)->update(['password' => password_hash($npw, PASSWORD_DEFAULT)]);
            if (!empty($check2)) {
                return response()->json(['code' => 0, 'msg' => '', 'data' => '更改密码成功'], 200);
            }
        }
        return response()->json(['code' => 1, 'msg' => '更改密码错误', 'data' => ''], 400);
    }

    public function todayCheck(Request $request)
    {
        $today         = date("Y-m-d");
        $group         = ($request->level === 2) ? NULL : $request->group;
        $account       = [];
        $self          = [];
        $todayNotCheck = [];
        $todayNotCheck = account::GetTodayNotCheck(account::OPEN, $today, $group);
        $checkSelf     = record::CheckSelf($request->accountid, $today);
        foreach ($todayNotCheck as $key => $value) {
            $ava = empty($value['avatar']) ? '' : "/images/avatar/{$value['avatar']}.png";
            $account[$value['accountid']] = ['account' => $value['accountcode'], 'avatar' => $ava];
        }
        foreach ($checkSelf as $key => $value) {
            $self[$value['type']] = $value['date'];
        }
        $data = [
            'all'  => $account,
            'self' => $self
        ];
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function checkIn(Request $request)
    {
        $accountid = $request->accountid;
        $type      = $request->type;
        $ip        = $request->ip;
        $allType   = ['1' => '上班', '2' => '下班'];
        $cross     = [];
        $date      = date('Y-m-d');
        $now       = date('Y-m-d H:i:s');
        $check     = record::CheckRecord($accountid, $type, $date);
        if (!$check->isEmpty()) {
            return response()->json(['code' => 1, 'msg' => "{$allType[$type]}已打卡", 'data' => ''], 401);
        }

        //如果是跨天的人
        $res = shift_setting::whereStatus('1')->where('crossday', '1')->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $cross[] = $val['id'];
            }

            $res2 = account::where('accountid', $accountid)
                            ->whereIn('shift', $cross)
                            ->get();
            if (!$res2->isEmpty() && $type == '2') {
                $date = date('Y-m-d', strtotime("-1 Days"));
            }
        }

        $result = record::CheckIn($accountid, $type, $now, $date, $ip);
        if ($result) {
            return response()->json(['code' => 0, 'msg' => '', 'data' => "{$allType[$type]}打卡成功"], 200);
        }
    }

    public function getAliveAccount(Request $request)
    {
        $data     = [];
        $shift    = [];
        $allgroup = [];
        $allclass = [];
        $group    = ($request->level === 2) ? NULL : $request->group;
        $result    = group_setting::GetAllGroup();
        if(!$result->isEmpty()) {
            foreach ($result as $key => $val) {
                $allgroup[$val['id']] = $val['title'];
            }
        }

        $result    = group_setting::GetAllClass();
        if(!$result->isEmpty()) {
            foreach ($result as $key => $val) {
                $allclass[$val['id']] = $val['title'];
            }
        }

        $res = shift_setting::get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $shift[$val['id']] = $val['title'];
            }
        }

        $res = account::select('accountid', 'accountcode', 'nickname', 'level', 'group', 'shift', 'class')
                        ->where('status', '1')
                        ->when($group, function($q) use($group) {
                            $q->where('group', $group);
                        })
                        // ->orderBy('group', 'DESC')
                        // ->orderBy('accountcode', 'ASC')
                        ->orderBy('nickname', 'ASC')
                        ->distinct()
                        ->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $data[] = [
                            'id'          => $val['accountid'],
                            'accountcode' => $val['accountcode'],
                            'nickname'    => $val['nickname'],
                            'level'       => $val['level'],
                            'group'       => !empty($allgroup[$val['group']]) ? $val['group'] : $val['group'],
                            'class'       => !empty($allclass[$val['class']]) ? $val['class'] : '',
                            'shift'       => $val['shift']
                ];
            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function getMember(Request $request)
    {
        $data      = [];
        $allgroup  = [];
        $date      = !empty($request->date) ? $request->date : '';
        $starttime = date("Y-m-01 00:00:00", strtotime($date));
        $endtime   = date("Y-m-0t 23:59:59", strtotime($date));
        $group     = ($request->level === 2) ? null : $request->group;
        $result    = group_setting::GetAllGroup();
        if(!$result->isEmpty()) {
            foreach ($result as $key => $val) {
                $allgroup[$val['id']] = $val['title'];
            }
        }
        $res = account::join('account_record', 'account_record.accountid', '=', 'account.accountid')
            ->whereBetween('account_record.date', [$starttime, $endtime])
            ->where('account_record.status', '1')
            ->where('account.status', '1')
            ->when($group, function($q, $group) {
                $q->where('account.group', $group);
            })
            ->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $g = !empty($allgroup[$val['group']]) ? $allgroup[$val['group']] : $val['group'];
                $this->account_arr[$val['accountid']] = $val['accountid'];
                $data[$g][$val['accountid']] = [
                    'name' => $val['accountcode'],
                    'check' => ($val['check_report'] == '1') ? true : false,
                ];
            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function getMemberByDate(Request $request)
    {
        $data      = [];
        $return    = [];
        $acc_arr   = [];
        $starttime = date("Y-m-d 00:00:00", strtotime($request->date));
        $endtime   = date("Y-m-d 23:59:59", strtotime($request->date));
        $group     = ($request->level == 2) ? null : $request->group;
        //先找同部門的帳號
        $res = account::where('status', '1')
                    ->when($group, function($q, $group) {
                        $q->where('group', $group);
                    })
                    ->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $data[$val['accountid']] = [
                                            'id'         => $val['accountid'],
                                            'acc'        => $val['accountcode'],
                                            'checkin'    => false,
                                            'checkout'   => false,
                                            'remark_in'  => '',
                                            'remark_out' => '',
                ];
                $acc_arr[] = $val['accountid'];
            }
        }

        //撈出時間內有簽到的
        $res = record::whereBetween('date', [$starttime, $endtime])
                    ->whereIn('accountid', $acc_arr)
                    ->where('status', '1')
                    ->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                if($val['type'] == '1') {
                    $data[$val['accountid']]['checkin'] = true;
                } else if($val['type'] == '2') {
                    $data[$val['accountid']]['checkout'] = true;
                }
            }
        }

        //撈出時間內有备注的
        $res = account_remark::where('date', $request->date)
                            ->whereIn('accountid', $acc_arr)
                            ->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                if($val['type'] == '1') {
                    $data[$val['accountid']]['remark_in'] = $val['remark'];
                } else if($val['type'] == '2') {
                    $data[$val['accountid']]['remark_out'] = $val['remark'];
                }
            }
        }

        foreach ($data as $key => $val) {
            $return[] = [
                        'id'         => $val['id'],
                        'acc'        => $val['acc'],
                        'checkin'    => $val['checkin'],
                        'checkout'   => $val['checkout'],
                        'remark_in'  => $val['remark_in'],
                        'remark_out' => $val['remark_out'],
            ];
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $return], 200);
    }

    public function updateCheckReport(Request $request)
    {
        if (!empty($request->acc)) {
            if ($request->acc == 'all') {
                account::where('accountid', '!=', '')->update(['check_report' => '0']);
            } else {
                $status = empty($request->check) ? '0' : '1';
                account::where('accountid', $request->acc)->update(['check_report' => $status]);
            }
        }
    }

    //出缺勤紀錄
    public function getRecordLog(Request $request)
    {
        $accountid     = [$request->accountid]; //空值就全查
        $type          = ''; //空值就全查
        $starttime     = date("Y-m-01 00:00:00", strtotime($request->date. ' -1 Months'));
        //如果是人事報表就查到這個月一號
        $endtime       = date("Y-m-t 23:59:59", strtotime($request->date));
        $data          = [];
        $acc_shift     = [];
        $shift_setting = [];

        $res = account::whereStatus('1')->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $acc_shift[$val['accountid']] = $val["shift"];
            }
        }

        $res = shift_setting::whereStatus('1')->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $shift_setting[$val['id']] = [
                    "starttime" => $val["starttime"],
                    "endtime"   => $val["endtime"],
                ];
            }
        }

        $res = record::selectRaw('accountid, DATE_FORMAT(date, "%Y-%m-%d") as d, type, status, date')
            ->whereBetween('date', [$starttime, $endtime])
            ->when($accountid, function ($q, $accountid) {
                $q->whereIn('accountid', $accountid);
            })
            ->when($type, function ($q, $type) {
                $q->whereType($type);
            })
            ->whereStatus('1')
            ->orderBy('d', 'asc')
            ->get();
        if(!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                //如果是出缺勤紀錄就只需要查個人
                if (empty($type) && empty($data[$val['d']])) {
                    $data[$val['d']]['1']    = '';
                    $data[$val['d']]['2']    = '';
                    $data[$val['d']]['late'] = false;
                }
                $data[$val['d']][$val['type']] = date("H:i:s", strtotime($val['date']));
                //判斷是否遲到
                if ($val['type'] == '1') {
                    $data[$val['d']]['late'] = (date("H:i:s", strtotime($shift_setting[$acc_shift[$val['accountid']]]['starttime'])) < $data[$val['d']][$val['type']]) ? true : false;
                }

            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    //人事报表
    public function getRecord(Request $request)
    {
        $this->getMember($request);
        $accountid     = $this->account_arr; //空值就全查
        $type          = ''; //上下班空值就全查
        $starttime     = date("Y-m-01 00:00:00", strtotime($request->date));
        //如果是人事報表就查到這個月一號
        $endtime       = date("Y-m-01 23:59:59", strtotime($request->date." +1 Months"));
        $data          = [];
        $acc_shift     = [];
        $shift_setting = [];
        $shift_arr     = [];
        $cross_acc     = [];

        $res = shift_setting::whereStatus('1')->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $shift_setting[$val['id']] = [
                    "starttime" => $val["starttime"],
                    "endtime"   => $val["endtime"],
                ];

                //誇天的班別
                // if($val['crossday'] == '1') {
                //     $shift_arr[] = $val['id'];
                // }
            }
        }

        $res = account::whereStatus('1')->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $acc_shift[$val['accountid']] = $val["shift"];

                //跨天班別的人
                // if(in_array($val["shift"], $shift_arr)) {
                //     $cross_acc[] = $val['accountid'];
                // }
            }
        }

        $res = record::selectRaw('accountid, DATE_FORMAT(date, "%Y-%m-%d"), type, status, date, day')
                    ->whereBetween('date', [$starttime, $endtime])
                    ->when($accountid, function ($q, $accountid) {
                        $q->whereIn('accountid', $accountid);
                    })
                    ->when($type, function ($q, $type) {
                        $q->whereType($type);
                    })
                    ->whereStatus('1')
                    ->orderBy('day', 'asc')
                    ->get();
        if(!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $day = date('Y-m', strtotime($request->date));
                //過濾掉跨天班別的上個月底
                if($val['day'] < $day) continue;
                if(empty($type) && empty($data[$val['accountid']][$val['day']])) {
                    //整个月的初始值
                    //x=0會抓到上個月底
                    for ($x = 1; $x <= date('t', strtotime($request->date)); $x++) {
                        $allday = date("Y-m-d", strtotime("{$day}-{$x}"));
                        $data[$val['accountid']][$allday]['1']       = '';
                        $data[$val['accountid']][$allday]['2']       = '';
                        $data[$val['accountid']][$allday]['late1']   = ''; //遲到
                        $data[$val['accountid']][$allday]['late2']   = ''; //早退
                        $data[$val['accountid']][$allday]['total']   = ''; //工時
                        $data[$val['accountid']][$allday]['remark1'] = ''; //上班备注
                        $data[$val['accountid']][$allday]['remark2'] = ''; //下班备注
                        $data[$val['accountid']][$allday]['late']    = false;
                    }
                    $day2 = date('Y-m-01', strtotime($request->date. "+1 Months"));
                    $data[$val['accountid']][$day2]['1']       = '';
                    $data[$val['accountid']][$day2]['2']       = '';
                    $data[$val['accountid']][$day2]['late1']   = ''; //遲到
                    $data[$val['accountid']][$day2]['late2']   = ''; //早退
                    $data[$val['accountid']][$day2]['total']   = ''; //工時
                    $data[$val['accountid']][$day2]['remark1'] = ''; //上班备注
                    $data[$val['accountid']][$day2]['remark2'] = ''; //下班备注
                    $data[$val['accountid']][$day2]['late']    = false;
                }

                // if(in_array($val['accountid'], $cross_acc) && $val['type'] == '2') {
                //     $data[$val['accountid']][date("Y-m-d", strtotime($val['date']." -1 Days"))][$val['type']] = date("H:i:s", strtotime($val['date']));
                // } else {
                    $data[$val['accountid']][$val['day']][$val['type']] = date("H:i:s", strtotime($val['date']));
                // }


                //判斷是否遲到
                $acc_time = strtotime($data[$val['accountid']][$val['day']][$val['type']]);
                if ($val['type'] == '1') {
                    $start_time = strtotime($shift_setting[$acc_shift[$val['accountid']]]['starttime']);
                    if(($acc_time > $start_time)) {
                        //遲到
                        $h = str_pad(floor(($acc_time - $start_time) / 3600), 2, "0", STR_PAD_LEFT);
                        $i = str_pad(floor((($acc_time - $start_time) % 3600) / 60), 2, "0", STR_PAD_LEFT);
                        $s = str_pad(($acc_time - $start_time) % 60, 2, "0", STR_PAD_LEFT);
                        $data[$val['accountid']][$val['day']]['late']  = true;
                        $data[$val['accountid']][$val['day']]['late1'] = "{$h}:{$i}:{$s}";
                    }
                } else if ($val['type'] == '2') {
                    // if(in_array($val['accountid'], $cross_acc)) {
                    //     $acc_time = strtotime($data[$val['accountid']][date("Y-m-d", strtotime($val['day']." -1 Days"))][$val['type']]);
                    // }
                    $end_time = strtotime($shift_setting[$acc_shift[$val['accountid']]]['endtime']);
                    if(($acc_time < $end_time)) {
                        //早退
                        $h = str_pad(floor(($end_time - $acc_time) / 3600), 2, "0", STR_PAD_LEFT);
                        $i = str_pad(floor((($end_time - $acc_time) % 3600) / 60), 2, "0", STR_PAD_LEFT);
                        $s = str_pad(($end_time - $acc_time) % 60, 2, "0", STR_PAD_LEFT);
                        $data[$val['accountid']][$val['day']]['late']  = true;
                        $data[$val['accountid']][$val['day']]['late2'] = "{$h}:{$i}:{$s}";
                    }
                }
            }
        }

        //備註
        $res = account_remark::where('date', 'LIKE', "{$request->date}%")
                            ->whereIn('accountid', $accountid)
                            ->get();
        if(!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $type = ($val['type'] == '1') ? "remark1" : "remark2";
                $data[$val['accountid']][$val['date']][$type] = $val['remark'];
            }
        }

        //算實際上班時數
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                if(!empty($v['1']) && !empty($v['2'])) {
                    //3600是休息一小時
                    $times = (abs(strtotime($v['2']) - strtotime($v['1'])) > 3600) ? abs(strtotime($v['2']) - strtotime($v['1'])) - 3600 : abs(strtotime($v['2']) - strtotime($v['1']));
                    $h = str_pad(floor($times / 3600), 2, "0", STR_PAD_LEFT);
                    //8小時以上就算8小時
                    if($h >= '8') {
                        $data[$key][$k]['total'] = '08:00';
                    } else {
                        $i = str_pad(floor(($times % 3600) / 60), 2, "0", STR_PAD_LEFT);
                        $data[$key][$k]['total'] = "{$h}:{$i}";
                    }
                }
            }
        }
        $this->record = $data;
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function writeList(Request $request)
    {
        $data      = [];
        $auditData = [];
        $accountid = $request->accountid;
        $content   = $request->content;
        $now       = date("Y-m-d H:i:s");
        $data      = [
            'accountid' => $accountid,
            'content'   => $content,
            'status'    => '1',
            'date'      => $now,
        ];
        $check = do_list::whereAccountid($accountid)->get();
        // $method = $check->isEmpty() ? 'insert' : 'update';
        DB::begintransaction();
        $res = do_list::create($data);
        if (!empty($res->id)) {
            $auditData = [
                'mapping'    => $accountid,
                'method'     => 'insert',
                'para'       => json_encode($data),
                'updateuser' => $accountid,
                'date'       => $now
            ];
            $res2 = do_list_audit::create($auditData);
            if (!empty($res2->id)) {
                DB::commit();
                return response()->json(['code' => 0, 'msg' => '', 'data' => '新增成功'], 200);
            }
        }
        DB::rollback();
        return response()->json(['code' => 1, 'msg' => '新增失败'], 400);
    }

    public function getList(Request $request)
    {
        $group = ($request->level === 2) ? NULL : $request->group;
        $groupAccount = account::GetGroupAccount($group, account::OPEN);
        if (!$groupAccount->isEmpty()) {
            foreach ($groupAccount as $value) {
                $account[$value['accountid']] = $value['accountcode'];
                $avatar[$value['accountid']]  = (empty($value['avatar'])) ? '' : "images/avatar/{$value['avatar']}.png";
                $accountid[] = $value['accountid'];
            }
        }
        $data[$account[$request->accountid]] = [];
        foreach ($avatar as $key => $value) {
            $data[$account[$key]]['avatar'] = $value;
        }
        $res = do_list::where('status', '<>', '0')->whereIn('accountid', $accountid)->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                // $data[$account[$val['accountid']]][$val['id']]['content'] = $val['content'];
                // $data[$account[$val['accountid']]][$val['id']]['status']  = $val['status'];
                $data[$account[$val['accountid']]]['avatar'] = $avatar[$val['accountid']];
                $data[$account[$val['accountid']]]['list'][$val['id']] = ['content' => $val['content'], 'status' => $val['status']];
            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function updateList(Request $request)
    {
        $id     = $request->id;
        $status = $request->status;
        $update = do_list::ChangeStatus($id, $status);
        if (!$update)
            return response()->json(['code' => 1, 'msg' => '修改失败', 'data' => ''], 400);
        return response()->json(['code' => 0, 'msg' => '', 'data' => '修改成功'], 200);
    }

    public function delList(Request $request)
    {
        $accountid = $request->accountid;
        $delId     = $request->id;
        $check     = 0;
        $res       = do_list::whereId($delId)->get();
        if (!$res->isEmpty()) {
            DB::begintransaction();
            $check = do_list::whereId($delId)->update(['status' => '0']);
            if (!empty($check)) {
                $data = [
                    'mapping'    => $delId,
                    'method'     => 'delete',
                    'para'       => json_encode(['status' => '0']),
                    'updateuser' => $accountid,
                    'date'       => date("Y-m-d H:i:s")
                ];
                $resAudit = do_list_audit::create($data);
                if (!empty($resAudit->id)) {
                    DB::commit();
                    return response()->json(['code' => 0, 'msg' => '', 'data' => '删除成功'], 200);
                }
            }
            DB::rollback();
            return response()->json(['code' => 1, 'msg' => '删除失败', 'data' => ''], 400);
        }
    }

    public function getShiftSetting(Request $request)
    {
        $data = [];
        $status = $request->status;
        $res = shift_setting::when($status, function ($q) use ($status) {
            //如果是2(無效) 就查0
            $status = ($status === '2') ? '0' : $status;
            $q->whereStatus($status);
        })
            ->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $data[] = $val;
            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function delShiftSetting(Request $request)
    {
        $result = account::whereStatus('1')->where('shift', $request->id)->get();
        if ($result->isEmpty()) {
            DB::begintransaction();
            $res = shift_setting::whereStatus('1')->whereId($request->id)->update(['status' => '0']);
            if (!empty($res)) {
                $data = [
                    'mapping'    => $request->id,
                    'method'     => 'update',
                    'para'       => json_encode(['status' => '0']),
                    'updateuser' => $request->accountid,
                    'date'       => date("Y-m-d H:i:s")
                ];
                $res2 = shift_audit::create($data);
                if (!empty($res2->id)) {
                    DB::commit();
                    return response()->json(['code' => 0, 'msg' => '', 'data' => '删除成功'], 200);
                }
            }
            DB::rollback();
            return response()->json(['code' => 1, 'msg' => '删除失败', 'data' => ''], 400);
        } else {
            return response()->json(['code' => 1, 'msg' => '该班别还有员工', 'data' => ''], 400);
        }
    }

    public function editShiftSetting(Request $request)
    {
        $result = shift_setting::where('id', $request->id)->get();
        if (!$result->isEmpty()) {
            DB::begintransaction();
            $data = [
                'title'     => $request->title,
                'starttime' => $request->time[0],
                'endtime'   => $request->time[1],
                'crossday'  => $request->crossday,
            ];
            $res = shift_setting::where('id', $request->id)->update($data);
            if (!empty($res)) {
                $data = [
                    'mapping'    => $request->id,
                    'method'     => 'update',
                    'para'       => json_encode($data),
                    'updateuser' => $request->accountid,
                    'date'       => date("Y-m-d H:i:s")
                ];
                $res2 = shift_audit::create($data);
                if (!empty($res2->id)) {
                    DB::commit();
                    return response()->json(['code' => 0, 'msg' => '', 'data' => '修改成功'], 200);
                }
            }
            DB::rollback();
            return response()->json(['code' => 1, 'msg' => '修改失败', 'data' => ''], 400);
        } else {
            return response()->json(['code' => 1, 'msg' => '没有此班别', 'data' => ''], 400);
        }
    }

    public function addShiftSetting(Request $request)
    {
        if (empty($request->title)) {
            return response()->json(['code' => 1, 'msg' => '请输入班别', 'data' => ''], 400);
        } else if (empty($request->time[0])) {
            return response()->json(['code' => 1, 'msg' => '请输入开始时间', 'data' => ''], 400);
        } else if (empty($request->time[1])) {
            return response()->json(['code' => 1, 'msg' => '请输入结束时间', 'data' => ''], 400);
        } else {
            DB::begintransaction();
            $data = [
                'title'     => $request->title,
                'starttime' => $request->time[0],
                'endtime'   => $request->time[1],
                'status'    => '1',
            ];
            $res = shift_setting::create($data);
            if (!empty($res)) {
                $data2 = [
                    'mapping'    => $res->id,
                    'method'     => 'insert',
                    'para'       => json_encode($data),
                    'updateuser' => $request->accountid,
                    'date'       => date("Y-m-d H:i:s")
                ];
                $res2 = shift_audit::create($data2);
                if (!empty($res2->id)) {
                    DB::commit();
                    return response()->json(['code' => 0, 'msg' => '', 'data' => '新增成功'], 200);
                }
            }
            DB::rollback();
            return response()->json(['code' => 1, 'msg' => '新增失败', 'data' => ''], 400);
        }
    }

    public function getAccShift(Request $request)
    {
        $data = [
            '1' => '',
            '2' => '',
        ];
        $res = shift_setting::whereId($request->shift)->whereStatus('1')->get();
        if (!$res->isEmpty()) {
            $data = [
                '1' => $res->first()->starttime,
                '2' => $res->first()->endtime,
            ];
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function editAccount(Request $request)
    {
        $editData = [
            'nickname' => $request->nickname,
            'level'    => $request->editLevel,
            'group'    => $request->editGroup,
            'class'    => $request->editClass,
            'shift'    => $request->editShift
        ];
        if(!isset($request->editLevel) || !isset($request->editGroup) || !isset($request->editClass) || !isset($request->editShift)) {
            return response()->json(['code' => 1, 'msg' => '栏位不可为空'], 400);
        }
        $update = account::where('accountid', $request->id)->update($editData);
        return response()->json(['code' => 0, 'msg' => '', 'data' => '更改完成'], 200);
    }

    public function editAccountCheckin(Request $request)
    {
        if(!empty($request->status)) {
            return response()->json(['code' => 1, 'msg' => '无法帮员工打卡', 'data' => ''], 400);
        } else if(empty($request->type) || !in_array($request->type, ['1', '2'])) {
            return response()->json(['code' => 1, 'msg' => '更改失败', 'data' => ''], 400);
        }

        $res = record::where('accountid', $request->id)
                    ->where('type', $request->type)
                    ->where('date', 'like', "%{$request->date}%")
                    ->update(['status' => '0']);
        if (!empty($res)) {
            return response()->json(['code' => 0, 'msg' => '', 'data' => '更改成功'], 200);
        }
        return response()->json(['code' => 1, 'msg' => '更改失败', 'data' => ''], 400);
    }

    public function delAccount(Request $request)
    {
        $id     = $request->id;
        $update = account::where('accountid', $id)->update(['status' => '0']);
        if(!$update)
            return response()->json(['code' => 1, 'msg' => '删除失败', 'data' => ''], 400);
        return response()->json(['code' => 0, 'msg' => '', 'data' => '删除成功'], 200);
    }

    public function editRemark(Request $request)
    {
        if(empty($request->id) || empty($request->type) || empty($request->date)) {
            return response()->json(['code' => 1, 'msg' => '更新失败', 'data' => ''], 400);
        }
        $check = 'insert';
        $data = [
                    'accountid' => $request->id,
                    'type'      => $request->type,
                    'date'      => $request->date,
                    'remark'    => $request->remark
                ];
        $res = account_remark::where('accountid', $request->id)->where('type', $request->type)->where('date', $request->date)->get();
        if(!$res->isEmpty()) {
            $check = 'update';
            $data = ['remark' => $request->remark];
        }

        DB::begintransaction();
        $res = account_remark::updateOrCreate(['accountid' => $request->id, 'type' => $request->type, 'date' => $request->date], ['remark' => $request->remark]);
        if(!empty($res)) {
            $data2 = [
                'mapping'    => $res->id,
                'method'     => $check,
                'para'       => json_encode($data),
                'updateuser' => $request->accountid,
                'date'       => date("Y-m-d H:i:s")
            ];
            $res2 = account_remark_audit::create($data2);
            if (!empty($res2->id)) {
                DB::commit();
                return response()->json(['code' => 0, 'msg' => '', 'data' => '更新成功'], 200);
            }
        }
        DB::rollback();
        return response()->json(['code' => 1, 'msg' => '更新失败', 'data' => ''], 400);
    }

    public function export(Request $request)
    {
        $account = [];
        $data    = [];
        $accounts = account::GetAllAccount(account::OPEN);
        foreach($accounts as $val) {
            $account[$val['accountid']]['accountcode'] = $val['accountcode'];
            $account[$val['accountid']]['nickname'] = $val['nickname'];
        }
        $data = [['员工编号','帐号', '日期', '上班', '下班', '是否迟到', '迟到时数', '早退时数', '实际工时', '上班备注', '下班备注']];
        $this->getRecord($request);
        foreach($this->record as $key => $value) {
            foreach($value as $k => $v) {
                $data[] = [
                                'nickname' => $account[$key]['nickname'], //员工编号
                                'account'  => $account[$key]['accountcode'], //帐号
                                'date'     => $k, //日期
                                '1'        => $v['1'], //上班
                                '2'        => $v['2'], //下班
                                'late'     => ($v['late'] == 'TRUE') ? '是' : '否', //是否迟到
                                'late1'    => $v['late1'], //迟到时数
                                'late2'    => $v['late2'], //早退时数
                                'total'    => $v['total'], //实际工时
                                'remark1'  => $v['remark1'], //上班备注
                                'remark2'  => $v['remark2'], //下班备注
                ];
            }
        }
        return Excel::download(new ReportExport($data), 'test.xlsx');
    }

    public function getAvatar(Request $request)
    {
        $accountAvatar = account::GetAccountAvatar([$request->accountid]);
        $avatarId      = $accountAvatar[$request->accountid];
        $pic           = avatar::GetPic($avatarId);
        $path          = "images/avatar/{$avatarId}.png";
        if (!empty($pic) && !File::exists($path)) {
            $this->build($pic, $path);
        }
        $img = empty($avatarId) ? '' : $path."?".date("YmdHi");
        if (empty($accountAvatar) || empty($avatarId))
            return response()->json(['code' => 1, 'msg' => '取得头贴失败', 'data' => ''], 400);
        return response()->json(['code' => 0, 'msg' => '', 'data' => $img], 200);

    }

    public function changeAvatar(Request $request)
    {
        $id = avatar::InsertPic($request->pic);
        account::where('accountid', $request->accountid)->update(['avatar' => $id]);
        if ($id) {
            return response()->json(['code' => 0, 'msg' => '', 'data' => '更改成功'], 200);
        } else {
            return response()->json(['code' => 0, 'msg' => '', 'data' => '更改失败'], 400);
        }
    }

    public function getGroup()
    {
        $data = [];
        $return = [];
        $res = group_setting::where('status', '1')
                            ->get();
        if(!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                if($val['level'] == '0') {
                    $data[$val['id']] = [
                                            'id'       => $val['id'],
                                            'title'    => $val['title'],
                                            'class'    => [],
                                            'class_ch' => [],
                                        ];
                }
                if($val['level'] == '1' && !empty($val['parent'])) {
                    // $data[$val['parent']]['class'][]    = $val;
                    $data[$val['parent']]['class'][]    = (Int)$val['id'];
                    $data[$val['parent']]['class_ch'][] = $val['title'];
                }
            }

            if(count($data) > 0) {
                foreach ($data as $key => $val) {
                    $return[] = [
                                    'id'       => $val['id'],
                                    'title'    => $val['title'],
                                    'class'    => $val['class'],
                                    'class_ch' => implode('/', $val['class_ch']),
                                ];
                }
            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $return], 200);
    }

    public function getClass(Request $request)
    {
        $data     = [];
        $allgroup = [];
        $type     = $request->type;
        $result    = group_setting::GetAllGroup();
        if(!$result->isEmpty()) {
            foreach ($result as $key => $val) {
                $allgroup[$val['id']] = $val['title'];
            }
        }
        $res = group_setting::where('status', '1')
                            ->where('level', '1')
                            ->when($type, function ($q,$type) {
                                $q->where('parent', $type);
                            })
                            ->get();
        if(!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                $val['groupName'] = !empty($allgroup[$val['parent']]) ? $allgroup[$val['parent']] : '';
                $data[] = $val;
            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function updateGroup(Request $request)
    {
        $id     = $request->group_id;
        $title  = $request->group_title;
        DB::begintransaction();
        $res = group_setting::where('id', $id)->where('level', '0')->update(['title' => $title]);
        if(!empty($res)) {
            $data = [
                'mapping'    => $id,
                'method'     => 'update',
                'para'       => json_encode(['title' => $title]),
                'updateuser' => $request->accountid,
                'date'       => date("Y-m-d H:i:s")
            ];
            $res2 = group_audit::create($data);
            if (!empty($res2->id)) {
                DB::commit();
                return response()->json(['code' => 0, 'msg' => '', 'data' => '更新部门成功'], 200);
            }
        }
        DB::rollback();
        return response()->json(['code' => 1, 'msg' => '更新部门失败', 'data' => ''], 400);
    }

    public function insertClass(Request $request)
    {
        $data = [
                    'title'  => $request->class_title,
                    'level'  => '1',
                    'parent' => $request->class_group,
                    'status' => '1',
        ];
        DB::begintransaction();
        $res = group_setting::create($data);
        if(!empty($res)) {
            $data2 = [
                'mapping'    => $res->id,
                'method'     => 'insert',
                'para'       => json_encode($data),
                'updateuser' => $request->accountid,
                'date'       => date("Y-m-d H:i:s")
            ];
            $res2 = group_audit::create($data2);
            if (!empty($res2->id)) {
                DB::commit();
                return response()->json(['code' => 0, 'msg' => '', 'data' => '新增组别成功'], 200);
            }
        }
        DB::rollback();
        return response()->json(['code' => 1, 'msg' => '新增组别失败', 'data' => ''], 400);
    }

    public function updateClass(Request $request)
    {
        $id     = $request->insert_id;
        $title  = $request->insert_title;
        $parent = $request->insert_group;
        DB::begintransaction();
        $res = group_setting::where('id', $id)->where('level', '1')->update(['title' => $title, 'parent' => $parent]);
        if(!empty($res)) {
            $data = [
                'mapping'    => $id,
                'method'     => 'update',
                'para'       => json_encode(['title' => $title, 'parent' => $parent]),
                'updateuser' => $request->accountid,
                'date'       => date("Y-m-d H:i:s")
            ];
            $res2 = group_audit::create($data);
            if (!empty($res2->id)) {
                DB::commit();
                return response()->json(['code' => 0, 'msg' => '', 'data' => '更新组别成功'], 200);
            }
        }
        DB::rollback();
        return response()->json(['code' => 1, 'msg' => '更新组别失败', 'data' => ''], 400);
    }

    public function delGroup(Request $request)
    {
        $id = $request->id;
        $check = account::where('group', $id)->where('status', '1')->get();
        if($check->isEmpty()) {
            DB::begintransaction();
            $res = group_setting::where('id', $id)->where('level', '0')->update(['status' => '0']);
            if(!empty($res)) {
                $data = [
                    'mapping'    => $id,
                    'method'     => 'update',
                    'para'       => json_encode(['status' => '0']),
                    'updateuser' => $request->accountid,
                    'date'       => date("Y-m-d H:i:s")
                ];
                $res2 = group_audit::create($data);
                if (!empty($res2->id)) {
                    DB::commit();
                    return response()->json(['code' => 0, 'msg' => '', 'data' => '删除部门成功'], 200);
                }
            }
            DB::rollback();
            return response()->json(['code' => 1, 'msg' => '删除部门失败', 'data' => ''], 400);
        } else {
            return response()->json(['code' => 1, 'msg' => '此部门还有人员，无法删除', 'data' => ''], 400);
        }
    }

    public function delClass(Request $request)
    {
        $id = $request->id;
        $check = account::where('class', $id)->where('status', '1')->get();
        if($check->isEmpty()) {
            DB::begintransaction();
            $res = group_setting::where('id', $id)->where('level', '1')->update(['status' => '0']);
            if(!empty($res)) {
                $data = [
                    'mapping'    => $id,
                    'method'     => 'update',
                    'para'       => json_encode(['status' => '0']),
                    'updateuser' => $request->accountid,
                    'date'       => date("Y-m-d H:i:s")
                ];
                $res2 = group_audit::create($data);
                if (!empty($res2->id)) {
                    DB::commit();
                    return response()->json(['code' => 0, 'msg' => '', 'data' => '删除组别成功'], 200);
                }
            }
            DB::rollback();
            return response()->json(['code' => 1, 'msg' => '删除组别失败', 'data' => ''], 400);
        } else {
            return response()->json(['code' => 1, 'msg' => '此组别还有人员，无法删除', 'data' => ''], 400);
        }
    }

    public function postAnn(Request $request)
    {
        $channel         = $request->channel;
        $data['sendId']  = $request->accountid;
        $data['content'] = $request->content;
        $data['account'] = account::GetAccount($request->accountid);
        $data['cdate']   = date('Y-m-d H:i:s');
        if ($channel === '0') {
            event(new Podcast($data));
        } else {
            event(new PrivateAnn($data, $channel));
        }
        announcement::create(['channel' => $channel, 'content' => $data['content'], 'announcer' => $data['sendId'], 'cdate' => $data['cdate']]);
    }

    public function getAnn(Request $request)
    {
        $group  = $request->group;
        $groups = group_setting::select('id', 'title')->where('status', '1')->pluck('title', 'id')->toArray();
        $result = announcement::GetGroupAnn($group);
        foreach ($result as $key => $value) {
            // $result[$key]['channelName'] = ($value['channel'] === '0') ? '公司' : $groups[$value['channel']];
            $data[$value['channel']]['name'] = ($value['channel'] === '0') ? '公司' : $groups[$value['channel']];
            $data[$value['channel']]['announcement'][] = $value;
        }
        $return = [
            'group'       => $groups,
            'announcemnt' => $result
        ];
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }

    public function getGroupSetting()
    {
        $data = [
                    'group'        => [],
                    'class'        => [],
                    'class_group'  => [],
                    'filter_group' => [],
                    'filter_class' => [],
        ];
        $res = group_setting::whereStatus('1')->get();
        if (!$res->isEmpty()) {
            foreach ($res as $key => $val) {
                if($val['level'] == '0') {
                    $data['group'][$val['id']] = $val['title'];
                    $data['filter_group'][] = [
                                                'text'  => $val['title'],
                                                'value' => $val['title'],
                    ];
                } else {
                    $data['class'][$val['parent']][$val['id']] = $val['title'];
                    $data['class_group'][$val['id']] = $val['title'];
                    $data['filter_class'][] = [
                                                'text'  => $val['title'],
                                                'value' => $val['title'],
                    ];
                }
            }
        }
        return response()->json(['code' => 0, 'msg' => '', 'data' => $data], 200);
    }




    // 储存图片
    public function build($pic, $path)
    {
		$image     = ImageManagerStatic::make($pic);
		$tmpResult = $image->save(public_path($path))->toOthers();
    }

    public function jwt($account)
    {
        $payload = [
            'iss'       => "jwt",                     // Issuer of the token
            'sub'       => $account['accountid'],     // Subject of the token
            'acc'       => $account['accountcode'],
            'level'     => $account['level'],
            'group'     => $account['group'],
            'groupName' => $account['groupName'],

            'shift'     => $account['shift'],
            'iat'       => time(),                    // Time when JWT was issued.
            'exp'       => time() + 6 * 60 * 60        // Expiration time
        ];
        return JWT::encode($payload, env('JWT_SECRET'));
    }
}
