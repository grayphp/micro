<?php

namespace app\web\controller;


class WelcomeController
{

    public function index()
    {
        return view('welcome', ['user' => DB()->users->select()->one()->get()]);
    }
}