<?php

namespace App\Controllers;
use App\Controllers\BaseController;

class teste extends BaseController
{
    public function X()
    {   
        echo view('common/header');
        echo view('index');
        echo view('common/footer');

        //teste
    }
}