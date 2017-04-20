<?php

$script_name = $_SERVER['SCRIPT_NAME'];
$explode =  explode('/', $script_name);

if($explode[1] == 'index.php'){
	$base_folder = '';
} else {
	$base_folder = $explode[1];
}

define('BASE_PATH', '/' . $base_folder);

session_start();

header('Content-Type: text/html; charset=utf-8');

require 'vendor/autoload.php';

if(getenv('APPLICATION_ENV') == 'dev') {
	SassCompiler::run("assets/scss/", "assets/css/");
	
	error_reporting(E_ALL);
	ini_set('display_errors', '1');
} else {
	error_reporting(0);
	ini_set('display_errors', '0');
}

$routes = new \Slim\Slim();
$routes->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

require 'config/routes.php';

$routes->run();