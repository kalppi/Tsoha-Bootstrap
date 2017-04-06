<?php

class ThreadController extends BaseController {
	public static function view($id) {
		parent::checkLoggedIn();

		$thread = Thread::get($id);
		$thread->markAsRead(self::getLoggedInUser());

		$messages = Message::allInThread($thread->id);

		$first = array_shift($messages);

		View::make('thread.html', array(
			'title' => 'keskustelu: ' . $thread->title,
			'thread' => $thread,
			'messages' => $messages,
			'message' => $first
		));	
	}

	public static function createNew() {
		$catId = isset($_POST['thread-category']) ? $_POST['thread-category'] : null;
		$title = isset($_POST['thread-title']) ? $_POST['thread-title'] : null;
		$msg = isset($_POST['thread-message']) ? $_POST['thread-message'] : null;
		$url = isset($_POST['url']) ? $_POST['url'] : '/';

		$cat = Category::get($catId);

		$thread = new Thread(array(
			'category' => $cat,
			'title' => $title
		));

		$message = new Message(array(
			'thread_id' => $thread->id,
			'user' => self::getLoggedInUser(),
			'message' => $msg
		));

		$errors = $thread->errors();
		$errors = array_merge($errors, $message->errors());

		if(count($errors) == 0) {
			$thread->save();
			$message->save();

			Redirect::to('/ketju/' . $thread->id);
		} else {
			Redirect::to($url, array(
				'thread_title' => $title,
				'thread_message' => $msg,
				'errors' => $errors
			));
		}
	}
}