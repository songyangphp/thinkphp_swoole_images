<?php
namespace app\controller;

use app\BaseController;

//dev.api.think.swoole.com:85
class Index extends BaseController
{
    public function index()
    {
        echo "hello think-swoole";
    }
}