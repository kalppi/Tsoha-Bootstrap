<?php

$get = array(
	'/' => 'CategoryController::view',
	'/alue/:id' => 'CategoryController::view',
	'/ketju/uusi' => 'ThreadController::createNew',
	'/hallinta' => 'AdminController::index',
	'/ketju/:id' => 'ThreadController::view',
	'/ketju/:id/vastaa/:mid' => 'ThreadController::reply',
	'/viesti/:id' => 'MessageController::view',
	'/jasen/kaikki' => 'UserController::all',
	'/liity' => 'UserController::join',
	'/kirjaudu' => 'UserController::login',
	'/ulos' => 'UserController::logout',
	'/jasen' => 'UserController::index',
	'/jasen/:id' => 'UserController::user'
);

$post = array(
	'/hallinta' => 'AdminController::index',
	'/liity' => 'UserController::join',
	'/kirjaudu' => 'UserController::login',
	'/ketju/:id/vastaa/:mid' => 'ThreadController::reply'
);

foreach($get as $route => $f) {
	$routes->get($route, $f);
}

foreach($get as $route => $f) {
	$routes->post($route, $f);
}

$routes->get('/(:page.*)', 'ErrorController::e404');