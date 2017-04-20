<?php

class ErrorController extends BaseController {
	public static function error($msg) {
		View::make('error.html', array(
			'message' => $msg
		));
	}

	public static function e404($page) {
		View::make('error.html', array(
			'message' => 'sivua "' . $page . '" ei löydy'
		));
	}
}

?>