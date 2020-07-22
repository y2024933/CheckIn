<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use Log;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $result = json_decode($this->check($request));
        if ($result->status == "success") {
            $request['accountid'] = $result->msg->id;
            $request['level']     = $result->msg->level;
            $request['group']     = $result->msg->group;
            $request['groupName'] = $result->msg->groupName;
            $request['shift']     = $result->msg->shift;
            return $next($request);
        } else {
            echo json_encode($result);
            exit;
        }
    }

    public function check(Request $request)
    {
        $token  = '';
        $token = $request->bearerToken();
        if (!$token)
            return json_encode(['status' => 'fail', 'msg' => 'Token取得失败']);
        try {
            $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch (ExpiredException $e) {
            return json_encode(['status' => 'fail', 'msg' => 'Token过期 请重新整理']);
        } catch (Exception $e) {
            return json_encode(['status' => 'fail', 'msg' => 'Token错误']);
        }
        return json_encode(['status' => 'success', 'msg' => [
            'id'        => $credentials->sub,
            'account'   => $credentials->acc,
            'level'     => $credentials->level,
            'group'     => $credentials->group,
            'groupName' => $credentials->groupName,
            'shift'     => $credentials->shift,
            ]
        ]);
    }

    public function jwtCheck(Request $request)
    {
        return $this->check($request);
    }
}
