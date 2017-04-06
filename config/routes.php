<?php

$routes->get('/', function() {
	CategoryController::view();
});

$routes->get('/alue/:id', function($id) {
	CategoryController::view($id);
});

$routes->post('/ketju/uusi', function() {
	ThreadController::createNew();
});

$routes->get('/hallinta', function() {
	AdminController::index();
});

$routes->post('/hallinta', function() {
	AdminController::index();
});

$routes->get('/uusi-viesti', function() {
	MainController::newMessage();
});

$routes->get('/ketju/:id', function($id) {
	ThreadController::view($id);
});

$routes->get('/viesti/:id', function($id) {
	MessageController::view($id);
});

$routes->get('/jasen/kaikki', function() {
	UserController::all();
});

$routes->get('/jasen/uusi', function() {
	UserController::join();
});

$routes->post('/jasen/uusi', function() {
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

$routes->get('/jasen', function() {
	UserController::index();
});

$routes->get('/jasen/:id', function($id) {
	UserController::user($id);
});
