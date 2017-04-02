<?php

class ThreadController extends BaseController {
	public static function view($id) {
		parent::checkLoggedIn();

		$messages = Message::allInThread($id);

		View::make('thread.html', array(
			'messages' => $messages
		));	
	}
}