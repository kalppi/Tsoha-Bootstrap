<?php

class ForumController extends BaseController {
	public static function index() {
		parent::checkLoggedIn();
		
		View::make('thread_list.html', array(
			'user' => parent::getLoggedInUser()
		));
	}
}

?>