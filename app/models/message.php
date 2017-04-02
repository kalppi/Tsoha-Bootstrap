<?php

class Message extends BaseModel {
	public $id, $thread_id, $parent_id, $sent, $message, $user, $depth;

	public function __construct($attributes) {
		parent::__construct($attributes);
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_user (thread_id, parent_id, user_id, sent, message) VALUES (:thread_id, :parent_id, :user_id, :sent, :message) RETURNING id'
		);

		$q->execute(array(
			'thread_id' => $this->thread_id,
			'parent_id' => $this->parent_id,
			'user_id' => $this->user->id,
			'sent' => $this->sent,
			'message' => $this->message
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	public static function allInThread($id) {
		$q = DB::connection()->prepare(
			'WITH RECURSIVE messages_path AS (
				(SELECT id, user_id, sent, parent_id, message, ARRAY[id]::INTEGER[] AS path, 1 AS depth
				FROM forum_message WHERE parent_id IS NULL AND thread_id = :id)

				UNION ALL

				(SELECT m.id, m.user_id, m.sent, m.parent_id, m.message, mp.path || m.parent_id, depth + 1 AS depth
				FROM forum_message m, messages_path mp
				WHERE m.parent_id = mp.id)
			) SELECT * FROM messages_path ORDER BY path ASC, sent ASC'
		);

		$q->execute(array('id' => $id));

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		$messages = array();

		foreach($rows as $row) {
			$messages[] = new Message($row);
		}

		return $messages;
	}
}

?>