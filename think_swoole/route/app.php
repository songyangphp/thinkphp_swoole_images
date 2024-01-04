<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
Route::get('/', 'Index/index');
Route::get('/asynctask/demo', 'AsyncTask/demo');
Route::get('/coroutine/demo', 'Coroutine/demo');
Route::get('/websocket/demo', 'Websocket/index');
Route::get('/redis/test', 'RedisTest/index');