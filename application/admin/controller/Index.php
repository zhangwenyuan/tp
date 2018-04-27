<?php
namespace app\admin\controller;

use think\Build;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {

        return view('login');
    }

}
