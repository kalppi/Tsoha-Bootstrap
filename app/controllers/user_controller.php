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
		if(isset($_POST['join-submit'])) {
			if(strlen($_POST['login-other']) > 0) {
				die();
			}

			$name = trim($_POST['join-name']);
			$email = trim($_POST['join-email']);
			$pw = $_POST['join-password'];
			$pw2 = $_POST['join-password2'];

			
			$hash = self::hashPassword($pw);

			$user = new User(array(
				'name' => $name,
				'email' => $email,
				'hash' => $hash,
				'admin' => false
			));

			$errors = $user->errors();

			if (strlen($pw) < 8) {
				$errors[] = 'Salasanan pitää olla vähintään 8 merkkiä';
			} else if($pw != $pw2) {
				$errors[] = 'Salasanat eivät täsmänneet';
			}

			if(count($errors) == 0) {
				$user->save();

				Redirect::to('/');
			} else {
				View::make('join.html', array(
					'join_name' => $name,
					'join_email' => $email,
					'errors' => $errors
				));
			}
		} else {
			View::make('join.html');
		}
	}

	public static function login() {
		if(isset($_POST['login-submit'])) {
			$email = $_POST['login-email'];
			$pw = $_POST['login-password'];
			$remember = isset($_POST['login-remember']) ? boolval($_POST['login-remember']) : false;

			$user = User::findBy('email', $email);

			$err = function() use($email) {
				Redirect::to('/kirjaudu', array(
					'login_email' => $email,
					'error' => 'Virheellinen sähköposti tai salasana'
				));
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

		$users = User::all();

		View::make('users.html', array(
			'title' => 'jäsenet',
			'users' => $users
		));
	}

	public static function user($id) {
		self::checkLoggedIn();

		$user = User::find($id);

		if($user) {
			$data = Message::allByUser($user);
			$stats = User::stats($user);

			View::make('user.html', array(
				'title' => sprintf('jäsen (%s)', $user->name),
				'view_user' => $user,
				'data' => $data,
				'stats' => $stats
			));
		} else {
			ErrorController::error('käyttäjää ei löydy');
		}
	}
}

?>