<?php
namespace app\admin\controller;

use think\Build;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function __construct()
    {
        $this->initialize();
    }

    public function initialize(){

    }

    public function index()
    {

        return view('login');
    }

}
