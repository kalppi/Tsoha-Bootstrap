<?php

class MainController extends BaseController {
	public static function newMessage() {
		View::make('new-message.html');
	}

	public static function thread() {
		View::make('thread.html');
	}
}
