<?php 

namespace Edalicio\EngineTemplate\Controllers;

use stdClass;

class HomeController
{

    public function index()
    {
        view('index', [
            'name' => 'index',
            'list' => [1,2,2,2,2,2,2]
        ] );
    }

}