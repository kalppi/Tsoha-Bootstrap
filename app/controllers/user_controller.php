<?php

class UserController extends BaseController {
	public static function index() {
		self::checkLoggedIn();

		$user = parent::getLoggedInUser();

		self::user($user->id);
	}

	private static function generateSalt() {
		return '$2y$12$' . bin2hex(openssl_random_pseudo_bytes(32));
	}

	private static function hashPassword($pw) {
		return crypt($pw, self::generateSalt());
	}

	private static function verifyPassword($pw, $hash) {
		 return $hash === crypt($pw, $hash);
	}

	public static function join() {
		if(isset($_POST['password'])) {
			$pw = $_POST['password'];
			$hash = self::hashPassword($pw);

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
			} else if(self::verifyPassword($pw, $user->hash)) {
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