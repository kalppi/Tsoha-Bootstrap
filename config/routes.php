<?php

$routes->get('/', function() {
	ForumController::index();
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
	UserController::join();
});

$routes->post('/kayttaja/uusi', function() {
	UserController::join();
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

$routes->get('/kayttaja', function() {
	UserController::index();
});

$routes->get('/kayttaja/:id', function($id) {
	UserController::user($id);
});