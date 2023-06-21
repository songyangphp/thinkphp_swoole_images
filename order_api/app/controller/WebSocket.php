<?php

namespace app\controller;

use app\BaseController;
//websocket用例 socketIO方式 监听逻辑在listener/WebSocketEvent.php中
class WebSocket extends BaseController
{
    public function index()
    {
        return view();
    }
}