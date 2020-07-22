<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Model\Account\a_account;
use App\Http\Middleware\JwtMiddleware;
use Log;

class JwtController extends Controller
{
	public function checkJwt(Request $request)
	{
		$jwt = new JwtMiddleware();
		if ($request->href != "/") {
			$href = substr($request->href, 1);
		} else {
			$href = $request->href;
		}
		$return = $jwt->jwtCheck($request);
		$de = json_decode($return);
		// if($de->status == "success"){
		// 	$this->setRedis($de->msg,$href);
		// }
		return $return;
	}

	public function setRedis($id, $href)
	{
		$this->refreshRedis();
		$accountcode = a_account::getCode($id);
		Redis::set($accountcode, "pc_" . $href);
		Redis::expire($accountcode, 300);
		Redis::sadd("users", $accountcode);
		$users = Redis::smembers("users");
	}

	public function refreshRedis()
	{
		$users = Redis::smembers("users");
		foreach ($users as $v) {
			if (!Redis::get($v)) {
				Redis::srem("users", $v);
			}
		}
	}
}
