<?php

class User extends BaseModel {
	public $id, $name, $hash, $email, $admin, $accepted, $registered, $deleted;

	public function __construct($attributes) {
		parent::__construct($attributes);

		$this->validators = array('validateName', 'validateEmail');
	}

	public function validateName() {
		$errors = array();

		if(empty($this->name)) {
			$errors[] = 'Nimi ei saa olla tyhjä';
		} else if(strlen($this->name) < 3) {
			$errors[] = 'Nimen pitää olla vähintään  3 merkkiä';
		}

		return $errors;
	}

	public function validateEmail() {
		$errors = array();

		if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = 'Virheellinen sähköpostiosoite';
		}

		return $errors;
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_user (name, hash, email, accepted, admin) VALUES (:name, :hash, :email, :accepted, :admin) RETURNING id'
		);

		$q->execute(array(
			'name' => $this->name,
			'hash' => $this->hash,
			'email' => $this->email,
			'accepted' => $this->accepted,
			'admin' => $this->admin
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	public function delete() {
		$q = DB::connection()->prepare(
			'DELETE FROM forum_user WHERE id = ?'
		);

		$q->execute(array($this->id));
	}

	public static function stats($user) {
		$q = DB::connection()->prepare(
			'WITH
				first_messages AS (
					SELECT DISTINCT ON (thread_id)
						id, thread_id, message, user_id
					FROM
						forum_message
					ORDER BY
						thread_id, sent ASC),
				messages AS (
					SELECT
						user_id, COUNT(*) AS count
					FROM forum_message
					GROUP BY user_id)

			SELECT
				u.id AS user_id,
				COUNT(fm.*) AS start_count,
				COUNT(fm.*)::float / (SELECT COUNT(*) FROM forum_thread) as start_percent,
				m.count AS message_count,
				m.count::float / (SELECT COUNT(*) FROM forum_message) as message_percent
			FROM forum_user u
			INNER JOIN first_messages fm ON fm.user_id = u.id
			INNER JOIN messages m ON m.user_id = u.id
			WHERE u.id = :user_id
			GROUP BY fm.user_id, u.id, m.user_id, m.count'
		);

		$q->execute(array('user_id' => $user->id));
		$row = $q->fetch(PDO::FETCH_ASSOC);

		return (object)$row;
	}

	public static function all() {
		$q = DB::connection()->prepare('SELECT * FROM forum_user');
		$q->execute();
		
		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		$users = array();

		foreach($rows as $row) {
			$users[] = new User($row);
		}

		return $users;
	}

	public static function allAccepted() {
		$q = DB::connection()->prepare('SELECT * FROM forum_user WHERE accepted = TRUE ORDER BY name ASC');
		$q->execute();
		
		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		$users = array();

		foreach($rows as $row) {
			$users[] = new User($row);
		}

		return $users;
	}

	public static function find($id) {
		$q = DB::connection()->prepare('SELECT * FROM forum_user WHERE id = :id LIMIT 1');
		$q->execute(array('id' => $id));

		$row = $q->fetch(PDO::FETCH_ASSOC);

		if($row) {
			return new User($row);
		} else {
			return null;
		}
	}

	public static function findBy($key, $val) {
		$q = DB::connection()->prepare('SELECT * FROM forum_user WHERE ' . $key . ' = :val LIMIT 1');
		$q->execute(array('val' => $val));

		$row = $q->fetch(PDO::FETCH_ASSOC);

		if($row) {
			return new User($row);
		} else {
			return null;
		}
	}
}

?>