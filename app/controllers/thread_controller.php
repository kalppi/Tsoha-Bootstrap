<?php

class ThreadController extends BaseController {
	public static function view($id) {
		parent::checkLoggedIn();

		$thread = Thread::get($id);
		$thread->markAsRead(self::getLoggedInUser());

		$messages = Message::allInThread($thread->id);

		$first = array_shift($messages);

		View::make('thread.html', array(
			'thread' => $thread,
			'messages' => $messages,
			'message' => $first
		));	
	}
}