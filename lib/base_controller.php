<?php

class BaseController {
	private static $loggedInUser = null;

	public static function getLoggedInUser() {
		if(self::$loggedInUser == null && !empty($_SESSION['token'])) {
			$loginToken = LoginToken::find($_SESSION['token']);

			if($loginToken) {
				self::$loggedInUser = User::find($loginToken->user_id);
				
				$loginToken->updateActive();
			}
		}

		return self::$loggedInUser;
	}

	public static function checkLoggedIn() {
		if(self::getLoggedInUser() == null) {
			View::make('login.html');
		}
	}

	public static function logout() {
		if(isset($_SESSION['token'])) {
			LoginToken::delete($_SESSION['token']);
			unset($_SESSION['token']);

			self::$loggedInUser = null;
		}

		Redirect::to('/');
	}
}
