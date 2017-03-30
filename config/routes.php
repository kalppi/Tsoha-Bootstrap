<?php

$routes->get('/', function() {
	ForumController::index();
});

$routes->get('/liity', function() {
	MainController::join();
});

$routes->get('/uusi-viesti', function() {
	MainController::newMessage();
});

$routes->get('/ketju', function() {
	MainController::thread();
});

$routes->get('/kayttaja/kaikki', function() {
	UserController::all();
});

$routes->get('/kayttaja/uusi', function() {
	UserController::create();
});

$routes->get('/kirjaudu', function() {
	UserController::login();
});

$routes->get('/ulos', function() {
	UserController::logout();
});

$routes->post('/kirjaudu', function() {
	UserController::login();
});