<?php

use Edalicio\EngineTemplate\Controllers\HomeController;

ini_set('display_errors', 1);
require_once __DIR__ ."/vendor/autoload.php";

// dd( config('template.cacheEnabled'));

view('index', ['list' => [1,2,3,4,5,6]]);
die;
(new HomeController)->index();