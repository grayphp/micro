<?php

namespace app\web\controller;

use SimpleCrud\Scheme\Sqlite;
use system\controller\Controller;

class WelcomeController extends Controller
{


    public function index()
    {
        return view('welcome');
    }
}