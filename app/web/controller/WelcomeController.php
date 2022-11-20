<?php

namespace app\web\controller;

use system\controller\Controller;

class WelcomeController extends Controller
{


    public function index()
    {
        return view('welcome');
    }
    function test()
    {
        var_dump(DB());
    }
}