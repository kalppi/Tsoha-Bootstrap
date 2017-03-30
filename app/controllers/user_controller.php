<?php

class UserController extends BaseController {
	public static function index() {

	}

	public static function create() {
		if(isset($_POST['password'])) {
			$pw = $_POST['password'];
			$hash = password_hash($pw, PASSWORD_DEFAULT);

			die($hash);

			$user = new User(array(
				'name' => $_POST['name'],
				'email' => $_POST['email'],
				'hash' => $hash
			));

			$user->save();
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
				echo 3;
				#Redirect::to('/');
			}
		} else {
			View::make('login.html');
		}
	}

	public static function all() {
		View::make('users.html', array('users' => User::all()));
	}
}

?>