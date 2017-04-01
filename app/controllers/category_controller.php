<?php

class CategoryController extends BaseController {
	public static function show($id = 'kaikki', $order = 'aloitus', $type = 'laskeva') {
		parent::checkLoggedIn();

		$cats = Category::all();
		array_unshift($cats, new Category(array('id' => 'kaikki', 'name' => 'Kaikki')));

		if($type == 'laskeva') {
			$ascdesc = 'DESC';
		} else {
			$ascdesc = 'ASC';
		}

		if($id == 'kaikki') {
			$threads = Thread::all($order, $ascdesc);
		} else {
			$threads = Thread::allInCategory(array($id), $order, $ascdesc);
		}

		View::make('threads-list.html', array(
			'cats' => $cats,
			'cat_selected' => $id,
			'order_selected' => $order,
			'type_selected' => $type,
			'threads' => $threads
		));
	}
}

?>