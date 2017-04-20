<?php

class Message extends BaseModel {
	public $id, $thread_id, $parent_id, $sent, $message, $user, $depth;

	public function __construct($attributes) {
		parent::__construct($attributes);

		$this->validators = array('validateMessage');
	}

	public function validateMessage() {
		$errors = array();

		if(empty($this->message)) {
			$errors[] = 'Viesti ei voi olla tyhjä';
		} else if(strlen($this->message) < 10) {
			$errors[] = 'Viestin on oltava vähintään 10 merkkiä';
		} else if(strlen($this->message) > 2000) {
			$errors[] = 'Viesti saa olal enintään 2000 merkkiä';
		}

		return $errors;
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_message (thread_id, parent_id, user_id, message) VALUES (:thread_id, :parent_id, :user_id, :message) RETURNING id'
		);

		$q->execute(array(
			'thread_id' => $this->thread_id,
			'parent_id' => $this->parent_id,
			'user_id' => $this->user->id,
			'message' => $this->message
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	public static function get($id) {
		$q = DB::connection()->prepare(
			'SELECT
				m.id, m.parent_id, m.sent, m.message, m.thread_id,
				u.id AS u_id, u.name AS u_name, u.admin AS u_admin
			FROM forum_message m
			INNER JOIN forum_user u ON u.id = m.user_id
			WHERE m.id = :id
			LIMIT 1'
		);

		$q->execute(array('id' => $id));
		$row = $q->fetch(PDO::FETCH_ASSOC);

		if(!$row) return null;

		$user = new User(array(
			'id' => $row['u_id'],
			'name' => $row['u_name'],
			'admin' => $row['u_admin']
		));

		$row['user'] = $user;

		return new Message($row);
	}

	public function getParent() {
		$q = DB::connection()->prepare(
			'SELECT
				m.id, m.parent_id, m.sent, m.message, m.thread_id,
				u.id AS u_id, u.name AS u_name, u.admin AS u_admin
			FROM forum_message m
			INNER JOIN forum_user u ON u.id = m.user_id
			WHERE m.id = :id
			LIMIT 1'
		);

		$q->execute(array('id' => $this->parent_id));
		$row = $q->fetch(PDO::FETCH_ASSOC);

		$user = new User(array(
			'id' => $row['u_id'],
			'name' => $row['u_name'],
			'admin' => $row['u_admin']
		));

		$row['user'] = $user;

		return new Message($row);
	}

	public function withChildren() {
		$q = DB::connection()->prepare(
			'WITH RECURSIVE messages_path AS (
				(SELECT id, user_id, sent, parent_id, message, ARRAY[id]::INTEGER[] AS path, 1 AS depth
				FROM forum_message WHERE parent_id IS NULL AND thread_id = :thread_id)

				UNION ALL

				(SELECT m.id, m.user_id, m.sent, m.parent_id, m.message, mp.path || m.id, depth + 1 AS depth
				FROM forum_message m, messages_path mp
				WHERE m.parent_id = mp.id)
			) SELECT m.id, m.sent AT TIME ZONE \'Europe/Helsinki\' AS sent, m.message, depth - array_position(path, :message_id) + 1 AS depth, m.parent_id, u.id AS u_id, u.name AS u_name, u.admin AS u_admin
				FROM messages_path m
				INNER JOIN forum_user u ON m.user_id = u.id
				WHERE :message_id = ANY (path)
				ORDER BY path ASC, sent ASC'
		);

		$q->execute(array('thread_id' => $this->thread_id, 'message_id' => $this->id));

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		$messages = array();

		foreach($rows as $row) {
			$user = new User(array(
				'id' => $row['u_id'],
				'name' => $row['u_name'],
				'admin' => $row['u_admin']
			));

			$row['user'] = $user;

			$messages[] = new Message($row);
		}

		return $messages;
	}

	public static function allByUser($user) {
		$q = DB::connection()->prepare(
			'SELECT m.id AS m_id, t.id AS t_id, t.title t_title, c.id AS c_id, c.name AS c_name, c.simplename AS c_simplename, m.sent AT TIME ZONE \'Europe/Helsinki\' AS m_sent,
			m2.id = m.id AS t_is_start,
			CASE WHEN length(m.message) > :max_length
				THEN substring(m.message from 1 for :max_length) || \'...\'
				ELSE m.message
			END as m_preview
			FROM forum_message m
			INNER JOIN forum_thread t ON t.id = m.thread_id
			INNER JOIN forum_category c ON c.id = t.category_id 
			INNER JOIN (
				SELECT
					m.thread_id, first_value(m.id) OVER w1 AS id, first_value(m.user_id) OVER w1 AS user_id
				FROM forum_message m
				WINDOW w1 AS (PARTITION BY m.thread_id ORDER BY sent ASC)
			) m2 ON m2.thread_id = t.id
			WHERE m.user_id = :user_id
			GROUP BY m.thread_id, m.id, t.id, t.title, c.id, c.name, m2.id, m2.user_id, m.sent, m.message
			ORDER BY m.sent DESC'
		);

		$q->execute(array('user_id' => $user->id, 'max_length' => 100));

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		$messages = array();

		foreach($rows as $row) {
			$messages[] = (object)$row;
		}

		return $messages;
	}

	public static function allInThread($id) {
		$q = DB::connection()->prepare(
			'WITH RECURSIVE messages_path AS (
				(SELECT id, user_id, sent, parent_id, message, ARRAY[id]::INTEGER[] AS path, 0 AS depth
				FROM forum_message WHERE parent_id IS NULL AND thread_id = :thread_id)

				UNION ALL

				(SELECT m.id, m.user_id, m.sent, m.parent_id, m.message, mp.path || m.id, depth + 1 AS depth
				FROM forum_message m, messages_path mp
				WHERE m.parent_id = mp.id)
			) SELECT m.id, m.sent AT TIME ZONE \'Europe/Helsinki\' AS sent, m.message, m.depth, m.parent_id, u.id AS u_id, u.name AS u_name, u.admin AS u_admin
				FROM messages_path m
				INNER JOIN forum_user u ON m.user_id = u.id
				ORDER BY path ASC, sent ASC, id ASC'
		);

		$q->execute(array('thread_id' => $id));

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		$messages = array();

		foreach($rows as $row) {
			$user = new User(array(
				'id' => $row['u_id'],
				'name' => $row['u_name'],
				'admin' => $row['u_admin']
			));

			$row['user'] = $user;

			$messages[] = new Message($row);
		}

		return $messages;
	}
}

?>