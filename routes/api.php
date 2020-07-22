<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', ['uses'=>'CheckInController@login']);

Route::group(['middleware' => 'jwt.auth'],function() {
    Route::post('checkIn', ['uses' => 'CheckInController@checkIn']);
    Route::post('register', ['uses' => 'CheckInController@createAccount']);
    Route::post('editAccount', ['uses' => 'CheckInController@editAccount']);
    Route::post('delAccount', ['uses' => 'CheckInController@delAccount']);
    Route::get('todayCheck', ['uses' => 'CheckInController@todayCheck']);
    Route::post('checkJwt', ['uses' => 'JwtController@checkJwt']);

    Route::post('getRecord', ['uses' => 'CheckInController@getRecord']);
    Route::post('getRecordLog', ['uses' => 'CheckInController@getRecordLog']);
    Route::post('getMember', ['uses' => 'CheckInController@getMember']);
    Route::post('getShiftSetting', ['uses' => 'CheckInController@getShiftSetting']);
    Route::post('delShiftSetting', ['uses' => 'CheckInController@delShiftSetting']);
    Route::post('editShiftSetting', ['uses' => 'CheckInController@editShiftSetting']);
    Route::post('addShiftSetting', ['uses' => 'CheckInController@addShiftSetting']);
    Route::get('getAccShift', ['uses' => 'CheckInController@getAccShift']);
    Route::post('updateCheckReport', ['uses' => 'CheckInController@updateCheckReport']);
    Route::get('getAliveAccount', ['uses' => 'CheckInController@getAliveAccount']);
    Route::post('getMemberByDate', ['uses' => 'CheckInController@getMemberByDate']);
    Route::post('editAccountCheckin', ['uses' => 'CheckInController@editAccountCheckin']);
    Route::post('editRemark', ['uses' => 'CheckInController@editRemark']);
    Route::post('changePassword', ['uses' => 'CheckInController@changePassword']);
    Route::post('changeAvatar', ['uses' => 'CheckInController@changeAvatar']);
    Route::get('getAvatar', ['uses' => 'CheckInController@getAvatar']);
    Route::get('getGroup', ['uses' => 'CheckInController@getGroup']);
    Route::post('getClass', ['uses' => 'CheckInController@getClass']);
    Route::post('insertClass', ['uses' => 'CheckInController@insertClass']);
    Route::post('updateClass', ['uses' => 'CheckInController@updateClass']);
    Route::post('delClass', ['uses' => 'CheckInController@delClass']);
    Route::post('insertGroup', ['uses' => 'CheckInController@insertGroup']);
    Route::post('updateGroup', ['uses' => 'CheckInController@updateGroup']);
    Route::post('delGroup', ['uses' => 'CheckInController@delGroup']);
    Route::post('getGroupSetting', ['uses' => 'CheckInController@getGroupSetting']);

    Route::get('getList', 'CheckInController@getList');
    Route::post('updateList', 'CheckInController@updateList');
    Route::post('writeList', 'CheckInController@writeList');
    Route::post('delList', 'CheckInController@delList');
    Route::get('exportReport', 'CheckInController@export');

    Route::post('postAnnouncement', ['uses' => 'CheckInController@postAnn']);
    Route::get('getAnnouncement', ['uses' => 'CheckInController@getAnn']);
});
