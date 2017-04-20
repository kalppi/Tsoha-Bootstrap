<?php

class CategoryController extends BaseController {
	public static function view($cat = 'all') {
		if($cat !== 'all' && !Category::exists($cat)) {
			ErrorController::error('virheellinen kategoria');
			return;
		}
 
		$cats = Category::all();
		array_unshift($cats, new Category(array(
			'id' => 'all',
			'name' => 'Kaikki',
			'simplename' => 'all',
			'thread_count' => Category::threadCount()
		)));

		$default = array(
			'category' => 'all',
			'orderField' => 'first',
			'order' => 'desc',
			'time' => 'all',
			'read' => 'all',
			'participated' => 'all'
		);

		$settings = $default;

		$settings['category'] = $cat;

		foreach(array_keys($default) as $k) {
			if(isset($_GET[$k])) {
				$settings[$k] = $_GET[$k];
			}
		}

		$threads = null;

		try {
			$threads = Thread::search($settings);
		} catch (Exception $e) {
			if($e instanceof PDOException) {
				echo "#";
			} else {
				ErrorController::error('virheellinen hakuparametri');
			}
			return;
		}

		View::make('threads-list.html', array(
			'url' => $_SERVER['QUERY_STRING'],
			'title' => 'keskustelu',
			'threads' => $threads,
			'default' => $default,
			'settings' => $settings,
			'settingsInfo' => array(
				'category' => $cats,
				'time' => array(
					'all' => 'Mikä tahansa',
					'month' => 'Kuukausi',
					'week' => 'Viikko',
					'day' => 'Vuorokausi'
				),
				'orderField' => array(
					'first' => 'Aloitus',
					'last' => 'Viimeisin',
					'messages' => 'Vastauksia',
					'read' => 'Luettu'
				),
				'order' => array(
					'desc' => array('Laskeva', 'glyphicon-sort-by-attributes-alt'),
					'asc' => array('Nouseva', 'glyphicon-sort-by-attributes')
				),
				'read' => array(
					'all' => 'Kaikki',
					'no' => 'Ei',
					'yes' => 'Kyllä'
				),
				'participated' => array(
					'all' => 'Kaikki',
					'no' => 'Ei',
					'yes' => 'Kyllä'
				)
			)
		));
	}
}

?>