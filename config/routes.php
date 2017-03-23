<?php

$routes->get('/', function() {
	HelloWorldController::index();
});

$routes->get('/liity', function() {
	HelloWorldController::join();
});
