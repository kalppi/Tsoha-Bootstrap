<?php

class UserController extends BaseController {
	public static function index() {
		self::checkLoggedIn();

		$user = parent::getLoggedInUser();

		self::user($user->id);
	}

	public static function join() {
		if(isset($_POST['password'])) {
			$pw = $_POST['password'];
			$hash = password_hash($pw, PASSWORD_DEFAULT);

			$user = new User(array(
				'name' => $_POST['name'],
				'email' => $_POST['email'],
				'hash' => $hash,
				'accepted' => false,
				'admin' => false
			));

			$user->save();

			View::make('joined.html');
		} else {
			View::make('join.html');
		}
	}

	public static function login() {
		if(isset($_POST['email'])) {
			$email = $_POST['email'];
			$pw = $_POST['password'];

			$user = User::findBy('email', $email);

			if(!$user) {
				Redirect::to('/kirjaudu');
			} else if(password_verify($pw, $user->hash)) {
				$loginToken = LoginToken::generate($user);

				//setcookie('login', $loginToken->token, 0, '/');
				$_SESSION['token'] = $loginToken->token;

				Redirect::to('/');
			} else {
				Redirect::to('/');
			}
		} else {
			View::make('login.html');
		}
	}

	public static function all() {
		self::checkLoggedIn();

		View::make('users.html', array('users' => User::all()));
	}

	public static function user($id) {
		self::checkLoggedIn();

		$user = User::find($id);

		if($user) {
			View::make('user.html', array('view_user' => $user));
		}
	}
}

?>