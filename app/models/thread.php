<?php

class Thread extends BaseModel {
	public $id, $category, $title, $message_count, $first_message, $last_message, $read_percent;

	public function __construct($attributes) {
		parent::__construct($attributes);
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_user (category_id, title) VALUES (:category_id, :title) RETURNING id'
		);

		$q->execute(array(
			'category_id' => $this->category->id,
			'title' => $this->title
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	private static function createThreads($rows, $reads, $order, $ascdesc) {
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
				'category' => new Category(array(
					'id' => $row['t_category_id'],
					'name' => $row['c_name']
				)),
				'title' => $row['t_title'],
				'message_count' => $row['t_message_count'],
				'first_message' => $firstMessage,
				'last_message' => $lastMessage,
				'read_percent' => isset($reads[$row['t_id']]) ? $reads[$row['t_id']] : 0
			));
		}

		if($order == 'luettu') {
			if($ascdesc == 'ASC') {
				$av = -1;
				$bv = 1;
			} else {
				$av = 1;
				$bv = -1;
			}

			usort($threads, function($a, $b) use($av, $bv) {
				return ($a->read_percent < $b->read_percent) ? $av : $bv;
			});
		}

		return $threads;
	}

	public static function all($order, $ascdesc) {
		$q = DB::connection()->prepare('SELECT thread_id, COUNT(*)::float / (SELECT COUNT(*) FROM forum_user WHERE accepted=TRUE) AS percent FROM forum_thread_read GROUP BY thread_id');

		$q->execute();

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);

		$reads = array();
		foreach($rows as $row) {
			$reads[$row['thread_id']] = $row['percent'];
		}

		switch($order) {
        	case "aloitus":
        		$sql = "SELECT DISTINCT ON(t.id, m.first_sent)";
        		break;
        	case "viimeisin":
        		$sql = "SELECT DISTINCT ON(t.id, m.last_sent)";
        		break;
        	case "viestejä":
        		$sql = "SELECT DISTINCT ON(t.id, t_message_count)";
        		break;
        	case "luettu":
        		$sql = "SELECT DISTINCT ON(t.id)";
        		break;
        	default:
        		throw new Exception('Unknown order (' . $order . ")");
    	}
        

        $sql .= " t.id AS t_id, t.title AS t_title, t.category_id AS t_category_id, t.title AS t_title, COUNT(m2.id) OVER (PARTITION BY t.id) AS t_message_count,
        m.first_id AS m_first_id, m.first_sent AT TIME ZONE 'Europe/Helsinki' AS m_first_sent, m.last_id AS m_last_id, m.last_sent AT TIME ZONE 'Europe/Helsinki' AS m_last_sent, m.first_message AS m_first_message, m.last_message as m_last_message,
        uf.id AS u_first_id, ul.id AS u_last_id, uf.name AS u_first_name, ul.name AS u_last_name, uf.admin AS u_first_admin, ul.admin AS u_last_admin, uf.registered AS u_first_registered, ul.registered AS u_last_registered,
        c.name AS c_name
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
        INNER JOIN forum_category c ON c.id = t.category_id
        GROUP BY t.id, m2.id, m.thread_id, m.first_id, m.last_id, m.first_sent, m.last_sent, c.name, m.first_message, m.last_message, uf.id, ul.id";
        
        switch($order) {
        	case "aloitus":
        		$sql .= " ORDER BY m.first_sent " . $ascdesc;
        		break;
        	case "viimeisin":
        		$sql .= " ORDER BY m.last_sent " . $ascdesc;
        		break;
        	case "viestejä":
        		$sql .= " ORDER BY t_message_count " . $ascdesc;
        		break;
    	}
        

       	$sql .= " LIMIT :limit OFFSET :offset";


		$q = DB::connection()->prepare($sql);

		$q->execute(array(
			'limit' => 100,
			'offset' => 0
		));

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		
		return self::createThreads($rows, $reads, $order, $ascdesc);
	}

	public static function allInCategory($cats, $order, $ascdesc) {
		$q = DB::connection()->prepare('SELECT thread_id, COUNT(*) AS count, category_id, COUNT(*)::float/(SELECT COUNT(*) FROM forum_user WHERE accepted=TRUE) AS percent FROM forum_thread_read ftr INNER JOIN forum_thread t ON t.id = ftr.thread_id  WHERE category_id IN (' . implode(',', $cats) . ') GROUP BY ftr.thread_id, t.category_id');

		$q->execute();

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);

		$reads = array();
		foreach($rows as $row) {
			$reads[$row['thread_id']] = $row['percent'];
		}

		switch($order) {
        	case "aloitus":
        		$sql = "SELECT DISTINCT ON(t.id, m.first_sent)";
        		break;
        	case "viimeisin":
        		$sql = "SELECT DISTINCT ON(t.id, m.last_sent)";
        		break;
        	case "luettu":
        		$sql = "SELECT DISTINCT ON(t.id)";
        		break;
    	}

        $sql .= " t.id AS t_id, t.title AS t_title, t.category_id AS t_category_id, t.title AS t_title, COUNT(m2.id) OVER (PARTITION BY t.id) AS t_message_count,
        m.first_id AS m_first_id, m.first_sent AT TIME ZONE 'Europe/Helsinki' AS m_first_sent, m.last_id AS m_last_id, m.last_sent AT TIME ZONE 'Europe/Helsinki' AS m_last_sent, m.first_message AS m_first_message, m.last_message as m_last_message,
        uf.id AS u_first_id, ul.id AS u_last_id, uf.name AS u_first_name, ul.name AS u_last_name, uf.admin AS u_first_admin, ul.admin AS u_last_admin, uf.registered AS u_first_registered, ul.registered AS u_last_registered,
        c.name AS c_name
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
        INNER JOIN forum_category c ON c.id = t.category_id
        WHERE c.id IN (" . implode(',', $cats) . ")
        GROUP BY t.id, m2.id, m.thread_id, m.first_id, m.last_id, m.first_sent, m.last_sent, c.name, m.first_message, m.last_message, uf.id, ul.id";

        switch($order) {
        	case "aloitus":
        		$sql .= " ORDER BY m.first_sent " . $ascdesc;
        		break;
        	case "viimeisin":
        		$sql .= " ORDER BY m.last_sent " . $ascdesc;
        		break;
    	}
        

       	$sql .= " LIMIT :limit OFFSET :offset";

		$q = DB::connection()->prepare($sql);

		$q->execute(array(
			'limit' => 100,
			'offset' => 0
		));

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		
		return self::createThreads($rows, $reads, $order, $ascdesc);
	}
}

?>