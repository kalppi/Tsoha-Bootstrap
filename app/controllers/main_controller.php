<?php

class MainController extends BaseController {
	public static function index() {
		View::make('thread_list.html');
	}

	public static function join() {
		View::make('join.html');
	}

	public static function newMessage() {
		View::make('new-message.html');
	}

	public static function thread() {
		View::make('thread.html');
	}
}
