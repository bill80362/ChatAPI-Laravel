<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


Route::post('/user/login', "User\Login");//登入
Route::post('/user/register', "User\Login@register");//註冊


//使用者 user 驗證Bearer Token
Route::group(['middleware' => 'auth:api','prefix'=>'user'], function () {
    //修改暱稱
//    Route::post('/nick', "User\UserController@Nick" );
    //加入好友
    Route::post('/friend',"User\UserController@addFriend" );
    //好友列表
    Route::get('/friend', "User\UserController@getFriends" );
    //刪除好友
    Route::delete('/friend', "User\UserController@delFriend" );
    //使用chat_id查詢
    Route::post('/check_user', "User\UserController@getUserbychatid" );
    //加我好友列表
    Route::get('/addmefriend', "User\UserController@whoaddme" );
});

//房間 room
Route::group(['middleware' => 'auth:api','prefix'=>'room'], function () {
    //創立房間
    Route::post('/room',"RoomController@create" );
    //修改房間名稱
    Route::put('/room', "RoomController@update" );
    //房間清單
    Route::get('/room', "RoomController@getRoomList" );

    //房間成員清單
    Route::post('/room_list', "RoomController@getUserList" );
    //邀請入房間
    Route::post('/room_user', "RoomController@joinRoom" );
    //移除房間
    Route::delete('/room_user', "RoomController@kickRoom" );
});

//訊息 Msg
Route::group(['middleware' => 'auth:api','prefix'=>'msg'], function () {
    //傳送訊息
    Route::post('/send',"MsgController@sendMsg" );
    //讀取歷史訊息
    Route::post('/read', "MsgController@readMsg" );
    //EventSource長連線

});
Route::get('/msg/stream', "MsgController@stream");//登入

Route::get('/msg/redis', "MsgController@redis");//登入
