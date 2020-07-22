<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\account;
use App\Models\account_record as record;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
// use RefreshDatabase;
class CheckinTest extends TestCase
{
    protected $account;

    public function jwt($accountid)
    {
        $payload = [
            'iss' => "jwt", // Issuer of the token
            'sub' => $accountid, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 7*24*60*60 // Expiration time
        ];
        return JWT::encode($payload, env('JWT_SECRET'));
    }

    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testcreateAccount()
    {
        echo "\n-----------创建帐号-----------\n";
        $data     = [];
        $account  = 'wendy';
        $name     = 'wendy';
        $password = '123';
        $now      = date("Y-m-d H:i:s");
        $pw       = password_hash($password, PASSWORD_DEFAULT);
        $data     = [
            'accountcode' => $account,
            'nickname'    => $name,
            'password'    => $pw,
            'status'      => '1',
            'lastlogin'   => $now,
            'cdate'       => $now
        ];
        // echo $result = account::create($data);
        $this->assertTrue(true);
    }

    public function login()
    {
        echo "\n-----------会员登入-----------\n";
        $accountcode = 'levi'; $password = '123';
        $getAccount  = account::Login($accountcode);
        try {
            if ($getAccount->isEmpty()) {
                throw new \Exception("无此帐号");
            }
            $account = $getAccount->toArray()[0];
            $check   = password_verify($password, $account['password']);
            if (!$check) {
                throw new \Exception("密码错误");
            } 
            if ($account['status'] !== '1') {
                throw new \Exception("帐号已被锁定");
            }
        } catch(\Exception $e) {
            echo "{$e->getMessage()}\n";exit;
        }
        print_r($account);
        $token = $this->jwt($account['accountid']);
        return $token;
    }
    
    public function testTodayCheck()
    {
        echo "\n-----------检查今日尚未签到-----------\n";
        $account       = [];
        $today         = date("Y-m-d");
        $todayNotCheck = [];
        $accounts      = account::GetAllAccount(account::OPEN);
        foreach ($accounts as $value) {
            $account[$value['accountid']] = $value['accountcode'];
        }
        $todayNotCheck = account::GetTodayNotCheck(account::OPEN, $today);
        print_r($todayNotCheck->toArray());
        $this->assertTrue(!$todayNotCheck->isEmpty());
    }

    public function testChecking()
    {
        $token = $this->login();
        echo "\n-----------会员签到-----------\n";
        $accountid = $this->checkJWT($token);
        $type      = '2';
        $allType   = ['1' => '上班', '2' => '下班'];
        $date      = date('Y-m-d'); $now = date('Y-m-d H:i:s');
        $check     = record::CheckRecord($accountid, $type, $date);
        if (!$check->isEmpty()) {
            echo "今日{$allType[$type]}已打卡";
            $this->assertTrue(false);
        }
        $result = record::CheckIn($accountid, $type, $now);
        if ($result) {
            echo "{$allType[$type]}打卡成功";
        }

        $this->assertTrue(true);

    }






    public function checkJWT($token)
    {
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch(ExpiredException $e) {
            return json_encode(['status' => 'fail', 'msg' => 'Provided token is expired']);
        } catch(Exception $e) {
            return json_encode(['status' => 'fail','msg' => 'An error while decoding token']);
        }

        return $accountid = empty($credentials->sub)?0:$credentials->sub;
    }
}
