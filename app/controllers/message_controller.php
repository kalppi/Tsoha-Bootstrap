<?php

class MessageController extends BaseController {
	public static function view($id) {
		$message = Message::get($id);

		if($message) {
			$thread = Thread::get($message->thread_id);
			$first = $thread->firstMessage();

			$messages = $message->withChildren();

			$parent = null;
			if($message->parent_id != null) {
				$parent = $message->getParent();
				array_unshift($messages, $parent);
			}

			View::make('message.html', array(
				'thread' => $thread,
				'message' => $first,
				'parent' => $parent,
				'messages' => $messages,
				'hilight_id' => $id
			));
		} else {
			ErrorController::error('viestiä ei löydy');
		}
	}
}