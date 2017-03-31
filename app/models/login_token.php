<?php

class LoginToken extends BaseModel {
	public $id, $token, $user_id, $last_active;

	public function __construct($attributes) {
		parent::__construct($attributes);
	}

	public static function generate($user) {
		$token = bin2hex(openssl_random_pseudo_bytes(32));

		$q = DB::connection()->prepare(
			'INSERT INTO forum_login_token (token, user_id) VALUES (:token, :user_id) RETURNING id, last_active'
		);

		$q->execute(array('token' => $token, 'user_id' => $user->id));

		$row = $q->fetch();
	    $id = $row['id'];
	    $active = $row['last_active'];

	    return new LoginToken(array(
	    	'id' => $id,
	    	'token' => $token,
	    	'user_id' => $user->id,
	    	'last_active' => $active
	    ));
	}

	public static function find($token) {
		$q = DB::connection()->prepare(
			'SELECT * FROM forum_login_token WHERE token = :token LIMIT 1'
		);

		$q->execute(array('token' => $token));
		$row = $q->fetch(PDO::FETCH_ASSOC);

		if(!$row) {
			return null;
		}

		return new LoginToken($row);
	}

	public static function delete($token) {
		$q = DB::connection()->prepare(
			'DELETE FROM forum_login_token WHERE token = :token'
		);

		$q->execute(array('token' => $token));
	}

	public function updateActive() {
		$q = DB::connection()->prepare('UPDATE forum_login_token SET last_active = NOW() WHERE id = :id');
		$q->execute(array('id' => $this->id));
	}
}

?>