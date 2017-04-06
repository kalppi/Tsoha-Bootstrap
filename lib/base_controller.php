<?php

class BaseController {
	private static $loggedInUser = null;

	public static function getLoggedInUser() {
		if(self::$loggedInUser == null) {
			$token = null;

			if(!empty($_SESSION['token'])) {
				$token = $_SESSION['token'];
			} else if(!empty($_COOKIE['token'])) {
				$token = $_COOKIE['token'];
			}

			if($token) {
				$loginToken = LoginToken::find($token);

				if($loginToken) {
					self::$loggedInUser = User::find($loginToken->user_id);
					
					$loginToken->updateActive();
				}
			}
		}

		return self::$loggedInUser;
	}

	public static function checkLoggedIn() {
		if(self::getLoggedInUser() == null) {
			View::make('login.html');
		}
	}

	public static function checkAdmin() {
		return self::checkLoggedIn() && self::getLoggedInUser()->admin == true;
	}

	public static function logout() {
		if(isset($_SESSION['token'])) {
			LoginToken::delete($_SESSION['token']);
			unset($_SESSION['token']);
			unset($_COOKIE['token']);

			self::$loggedInUser = null;
		}

		Redirect::to('/');
	}
}
