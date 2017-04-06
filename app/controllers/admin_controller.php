<?php

class AdminController extends BaseController {
	public static function index() {
		self::checkAdmin();

		if(isset($_GET['move-up'])) {
			$id = intval($_GET['move-up']);
			Category::moveUp($id);

			Redirect::to('/hallinta');
		} else if(isset($_GET['move-down'])) {
			$id = intval($_GET['move-down']);
			Category::moveDown($id);

			Redirect::to('/hallinta');
		}

		if(isset($_GET['delete'])) {
			$id = intval($_GET['delete']);
			$cat = Category::get($id);
			$cat->delete();

			Redirect::to('/hallinta');
		}

		if(isset($_POST['cat-name'])) {
			$name = trim($_POST['cat-name']);

			if(strlen($name) > 0) {
				$cat = new Category(array('name' => $name));
				$cat->save();
			}

			Redirect::to('/hallinta');
		}

		$cats = Category::all();

		View::make('admin.html', array(
			'cats' => $cats
		));
	}
}

?>