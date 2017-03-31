<?php

class ForumController extends BaseController {
	public static function index() {
		parent::checkLoggedIn();

		View::make('thread-list.html', array(
			'cats' => Category::all(),
			'threads' => Thread::all()
		));
	}
}

?>