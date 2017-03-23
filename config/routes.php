<?php

$routes->get('/', function() {
	MainController::index();
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