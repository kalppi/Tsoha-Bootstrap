<?php

class Thread extends BaseModel {
	public $id, $category_id, $title, $message_count, $first_message, $last_message;

	public function __construct($attributes) {
		parent::__construct($attributes);
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_user (category_id, title) VALUES (:category_id, :title) RETURNING id'
		);

		$q->execute(array(
			'category_id' => $this->category_id,
			'title' => $this->title
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	public static function all() {
		$sql = "SELECT DISTINCT ON(t.id, m.last_sent)
        t.id AS t_id, t.title AS t_title, t.category_id AS t_category_id, t.title AS t_title, COUNT(m2.id) OVER (PARTITION BY t.id) AS t_message_count,
        m.first_id AS m_first_id, m.first_sent AT TIME ZONE 'Europe/Helsinki' AS m_first_sent, m.last_id AS m_last_id, m.last_sent AT TIME ZONE 'Europe/Helsinki' AS m_last_sent, m.first_message AS m_first_message, m.last_message as m_last_message,
        uf.id AS u_first_id, ul.id AS u_last_id, uf.name AS u_first_name, ul.name AS u_last_name, uf.admin AS u_first_admin, ul.admin AS u_last_admin, uf.registered AS u_first_registered, ul.registered AS u_last_registered
        FROM forum_thread t
        INNER JOIN (
                SELECT
                        thread_id,
                        first_value(user_id) OVER w1 AS first_user_id,
                        first_value(id) OVER w1 AS first_id,
                        first_value(sent) OVER w1 AS first_sent,
                        first_value(message) OVER w1 AS first_message,
                        first_value(user_id) OVER w2 AS last_user_id,
                        first_value(id) OVER w2 AS last_id,
                        first_value(sent) OVER w2 AS last_sent,
                        first_value(message) OVER w2 AS last_message
                FROM forum_message
                WINDOW
                        w1 AS (PARTITION BY thread_id ORDER BY sent ASC),
                        w2 AS (PARTITION BY thread_id ORDER BY sent DESC)
        ) m ON t.id = m.thread_id
        INNER JOIN forum_message m2 ON t.id = m2.thread_id
        INNER JOIN forum_user uf ON uf.id = m.first_user_id
        INNER JOIN forum_user ul ON ul.id = m.last_user_id
        GROUP BY t.id, m2.id, m.thread_id, m.first_id, m.last_id, m.first_sent, m.last_sent, m.first_message, m.last_message, uf.id, ul.id
        ORDER BY m.last_sent DESC
        LIMIT :limit OFFSET :offset";


		$q = DB::connection()->prepare($sql);

		$q->execute(array(
			'limit' => 100,
			'offset' => 0
		));

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		$threads = array();

		foreach($rows as $row) {
			$firstUser = new User(array(
				'id' => $row['u_first_id'],
				'name' => $row['u_first_name'],
				'admin' => $row['u_first_admin']
			));

			$firstMessage = new Message(array(
				'id' => $row['m_first_id'],
				'thread_id' => $row['t_id'],
				'parent_id' => null,
				'sent' => $row['m_first_sent'],
				'message' => $row['m_first_message'],
				'user' => $firstUser
			));

			$lastUser = new User(array(
				'id' => $row['u_last_id'],
				'name' => $row['u_last_name'],
				'admin' => $row['u_last_admin']
			));

			$lastMessage = new Message(array(
				'id' => $row['m_last_id'],
				'thread_id' => $row['t_id'],
				'parent_id' => null,
				'sent' => $row['m_last_sent'],
				'message' => $row['m_last_message'],
				'user' => $lastUser
			));

			$threads[] = new Thread(array(
				'id' => $row['t_id'],
				'category_id' => $row['t_category_id'],
				'title' => $row['t_title'],
				'message_count' => $row['t_message_count'],
				'first_message' => $firstMessage,
				'last_message' => $lastMessage
			));
		}

		return $threads;
	}
}

?>