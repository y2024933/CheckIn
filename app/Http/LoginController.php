<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Account\a_account;
// use App\Model\Account\a_onlinetracker;
// use App\Model\Account\a_iptracker;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Redis;
use Log;
use Cookie;
class LoginController extends Controller
{

	protected  $time;

  	public function __construct()
  	{
  	  $this->time = date("Y-m-d H:i:s");
  	}

  	public function checkLogin(Request $request)
  	{
  	}

    public function login(Request $request)
    {
		$id = a_account::GetId($request->accountcode);
    	$check_login = "";
    	$accountcode = $request->accountcode;
    	$password = $request->password;
		$ip = empty($_SERVER["HTTP_X_FORWARDED_FOR"])?$_SERVER["REMOTE_ADDR"]:$_SERVER["HTTP_X_FORWARDED_FOR"];
		$ip_tmp = explode(",",$ip);
		$ip = $ip_tmp[0];
    	$data = json_decode($this->getAccountid($accountcode,$password,$ip));
    	if($data->status == "success"){
			$token = $this->jwt($data->accountid,$data->uid);
			Redis::set($data->accountid, $data->uid);
			//----------------POSTMAN用
			// $cookie = Cookie::make('token', $token,  7*24*60);
			// return \Response::make('body/login')->withCookie($cookie);
			//-----------------------
			//------------------表單用
			return response(array("status"=>"success","msg"=>$token))->withCookie(cookie('token_mobile', $token, 7*24*60));
			//-----------------------
    	}else{
            return json_encode(array('status'=>'fail','msg'=>$data->msg));
    	}
    }

    public function logout(Request $request)
    {
    	$accountid = $request->accountid;
    	$out = Redis::del($accountid);
    	Cookie::forget('token');
    	echo json_encode(array("status" => "success"));
    }


	public function getAccountid($accountcode,$password,$ip)
	{
		$check = a_account::where('accountcode',$accountcode)->get();
		if($check->first()) {
			$status = $check->first()->status;
			if($status != "1"){
				$status_array = array("-1" => "已关闭的帐号", "0" => "已无效的帐号", "2" => "已停权的帐号", "3" => "已暂停使用的帐号");
            	return json_encode(array('status'=>'fail','msg'=> $status_array[$status].'，请联系客服人员'));
			}
		}else {
            return json_encode(array('status'=>'fail','msg'=>'没有此帐号'));
		}
		$sql_password = $check->first()->password;
		if (!password_verify($password, $sql_password)) {
			$getCount = Redis::get($accountcode."_WrongCount");
			if(empty($getCount)){
				Redis::setex($accountcode."_WrongCount", 3600, 1);
            	return json_encode(array('status'=>'fail','msg'=>'密码不符合'));
			}elseif ($getCount < 5) {
				$count = Redis::incr($accountcode."_WrongCount");
            	return json_encode(array('status'=>'fail','msg'=>'密码不符合，错误五次将锁定帐号，已错误'.$count.'次'));
			}elseif ($getCount == 5) {
        		a_account::where('accountcode', $accountcode)->update(array("status" => "0"));
            	return json_encode(array('status'=>'fail','msg'=>'密码错误五次，帐号已锁定，请联系客服人员'));
			}
            return json_encode(array('status'=>'fail','msg'=>'密码不符合'));
		}
		$acid  = !empty($check->first()->accountid)?$check->first()->accountid:null;
		$agid  = !empty($check->first()->ag_accountid)?$check->first()->ag_accountid:null;
		$ag_accountcode = a_account::where('accountid',$agid)->where('status','1')->get();
		if($ag_accountcode){
			$return["ag_accountcode"] = !empty($ag_accountcode->first()->accountcode)?$ag_accountcode->first()->accountcode:null;
		}
		$onlinekey   = substr(md5($this->time),0,20);
		$host        = getenv('SERVER_NAME');
		$uid         = uniqid();
		// $checkonline = a_onlinetracker::where('accountid',$acid)->get();
		$rowdata = array("onlinekey" => $onlinekey, "onlineip" => $ip, "host" => $host);
		if($checkonline->first()) {
        		// a_onlinetracker::where('accountid',$acid)->update($rowdata);
		}else{
			$rowdata['accountid'] = $acid;
			// a_onlinetracker::create($rowdata);
		}
        a_account::where('accountid',$acid)->update(array("lastlogin"=>$this->time));
		$return["accountid"]    = $acid;
		$return["ag_accountid"] = $agid;
		$return["accountcode"]  = !empty($check->first()->accountcode)?$check->first()->accountcode:null;
		$return["companyindex"] = !empty($check->first()->companyindex)?$check->first()->companyindex:null;
		$return["levelid"]      = !empty($check->first()->levelid)?$check->first()->levelid:null;
		$return["onlinekey"]    = $onlinekey;
		$return["onlineip"]     = $ip;
		$return["uid"]          = $uid;
		$return["status"]          = "success";
    	// a_iptracker::create([
					// 					'accountid' => $acid,
					// 					'clientip' => $ip,
					// 					'host' => $host,
					// 					'cdate' => $this->time,
					// 				]);
    	$mapping = array('a_pt_account_mapping','a_gc_account_mapping');
    	foreach ($mapping as $key => $value) {
        	$app = "App\\Model\\Account\\".$value;
    		$pt = $app::where('accountindex',$acid)->get();
        	if($pt->first()) {
        		if($pt->first()->password != $password) {
        			$app::where('accountindex',$acid)->update(array("pwd"=>$password , "needchange"=>"1"));
       		 	}
        	}else {
    				$app::create([
													'accountindex' => $acid,
													'pwd' => $password,
													'needchange' => '1'
												]);
        	}
    	}
		return json_encode($return);
	}

    public function jwt($accountid,$uid)
    {
        $payload = [
            'iss' => "jwt", // Issuer of the token
            'aud' => $accountid,
            'sub' => $uid, // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + 7*24*60*60 // Expiration time
        ];
        return JWT::encode($payload, env('JWT_SECRET'));
    }


}
