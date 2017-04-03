<?php

class UserController extends BaseController {
	public static function index() {
		self::checkLoggedIn();

		$user = parent::getLoggedInUser();

		self::user($user->id);
	}

	private static function generateSalt() {
		return '$2a$12$' . bin2hex(openssl_random_pseudo_bytes(32));
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
		if(isset($_POST['email']) && isset($_POST['password'])) {
			$email = $_POST['email'];
			$pw = $_POST['password'];
			$remember = isset($_POST['remember']) ? boolval($_POST['remember']) : false;

			$user = User::findBy('email', $email);

			$err = function() {
				Redirect::to('/kirjaudu', array('error' => 'Virheellinen käyttäjätunnus tai salasana'));
			};

			if(!$user) {
				$err();
			} else if(self::verifyPassword($pw, $user->hash)) {
				$loginToken = LoginToken::generate($user);

				$_SESSION['token'] = $loginToken->token;
				
				if($remember) {
					setcookie('token', $loginToken->token, time() + 60 * 60 * 24 * 30, '/');
				}

				Redirect::to('/');
			} else {
				$err();
			}
		} else {
			View::make('login.html');
		}
	}

	public static function all() {
		self::checkLoggedIn();

		$users = User::allAccepted();

		View::make('users.html', array('users' => $users));
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