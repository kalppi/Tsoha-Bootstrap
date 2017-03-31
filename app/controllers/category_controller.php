<?php

class CategoryController extends BaseController {
	public static function index() {
		parent::checkLoggedIn();

		View::make('thread-list.html', array(
			'cats' => Category::all(),
			'cat_selected' => 'all',
			'threads' => Thread::all()
		));
	}

	public static function show($id) {
		parent::checkLoggedIn();

		View::make('thread-list.html', array(
			'cats' => Category::all(),
			'cat_selected' => $id,
			'threads' => Thread::allInCategory(array($id))
		));
	}
}

?>