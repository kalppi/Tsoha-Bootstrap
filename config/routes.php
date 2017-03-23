<?php

$routes->get('/', function() {
	HelloWorldController::index();
});

$routes->get('/liity', function() {
	HelloWorldController::join();
});

$routes->get('/uusi-viesti', function() {
	HelloWorldController::newMessage();
});