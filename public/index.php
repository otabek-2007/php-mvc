<?php
require __DIR__.'/../vendor/autoload.php';

use App\Core\Router;

$router = new Router();
$router->get('/', 'HomeController@index');
$router->run();
