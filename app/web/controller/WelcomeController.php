<?php

declare(strict_types=1);

namespace app\web\controller;

use system\controller\Controller;

class WelcomeController extends Controller
{
    public function index(): void
    {
        $this->view('welcome');
    }
}
