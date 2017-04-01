<?php

class CategoryController extends BaseController {
	public static function show() {
		parent::checkLoggedIn();

		$cats = Category::all();
		array_unshift($cats, new Category(array(
			'id' => 'kaikki',
			'name' => 'Kaikki',
			'thread_count' => Category::threadCount()
		)));

		$default = array(
			'alue' => 'kaikki',
			'jarjesta' => 'aloitus',
			'tyyppi' => 'laskeva',
			'aika' => 'kaikki'
		);

		$settings = $default;

		foreach(array_keys($default) as $k) {
			if(isset($_GET[$k])) $settings[$k] = $_GET[$k];
		}


		if($settings['tyyppi'] == 'laskeva') {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		if($settings['alue'] == 'kaikki') {
			$threads = Thread::all($settings['jarjesta'], $order);
		} else {
			$threads = Thread::allInCategory(array($settings['alue']), $settings['jarjesta'], $order);
		}

		View::make('threads-list.html', array(
			'cats' => $cats,
			'threads' => $threads,
			'default' => $default,
			'selected' => array(
				'cat' => $settings['alue'],
				'order' => $settings['jarjesta'],
				'type' => $settings['tyyppi'],
				'time' => $settings['aika']
			)
		));
	}
}

?>