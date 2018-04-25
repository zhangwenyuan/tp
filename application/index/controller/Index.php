<?php
namespace app\index\controller;

use think\Build;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        echo "Hello word!";die;
        $this->view('index');
    }


}
