<?php

class Message extends BaseModel {
	public $id, $thread_id, $parent_id, $sent, $message, $user;

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
}

?>