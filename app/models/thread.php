<?php

class Thread extends BaseModel {
	public $id, $category, $title, $message_count, $first_message, $last_message, $read_percent;

	public function __construct($attributes) {
		parent::__construct($attributes);

		$this->validators = array('validateTitle');
	}

	public function validateTitle() {
		$errors = array();

		if(empty($this->title)) {
			$errors[] = 'Otsikko ei voi olla tyhjä';
		}  else if(strlen($this->title) < 10) {
			$errors[] = 'Otsikon on oltava vähintään 10 merkkiä';
		} else if(strlen($this->title) > 100) {
			$errors[] = 'Otsikon saa olla enintään 100 merkkiä';
		}

		return $errors;
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_thread (category_id, title) VALUES (:category_id, :title) RETURNING id'
		);

		$q->execute(array(
			'category_id' => $this->category->id,
			'title' => $this->title
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	public function delete() {
		$q = DB::connection()->prepare(
			'DELETE FROM forum_thread WHERE id = ?'
		);

		$q->execute(array($this->id));
	}

	public function hasRead($user) {
		$q = DB::connection()->prepare(
			'SELECT COUNT(*) > 0 FROM forum_thread_read WHERE thread_id = :thread_id AND user_id = :user_id'
		);

		$q->execute(array('thread_id' => $this->id, 'user_id' => $user->id));

		return $q->fetchColumn();
	}

	public function markAsRead($user) {
		return;

		$q = DB::connection()->prepare(
			'
			WITH last_message AS (SELECT m.id FROM forum_thread t
						INNER JOIN forum_message m ON m.thread_id = t.id
						WHERE t.id = :thread_id
						ORDER BY sent DESC
						LIMIT 1)
			INSERT INTO
				forum_thread_read (thread_id, user_id, last_message_id)
			VALUES
				(
					:thread_id,
					:user_id,
					(SELECT id FROM last_message)
				)
			ON CONFLICT ON CONSTRAINT forum_thread_read_thread_id_user_id_key
			DO UPDATE SET last_message_id = (SELECT id FROM last_message)'
		);

		$q->execute(array('thread_id' => $this->id, 'user_id' => $user->id));
	}

	public function firstMessage() {
		$q = DB::connection()->prepare(
			'SELECT
				m.id, m.thread_id, m.parent_id, m.sent, m.message,
				u.id AS u_id, u.name AS u_name, u.admin AS u_admin
			FROM forum_message m
			INNER JOIN forum_user u ON u.id = m.user_id
			WHERE thread_id = :thread_id
			ORDER BY sent ASC LIMIT 1'
		);

		$q->execute(array('thread_id' => $this->id));

		$row = $q->fetch(PDO::FETCH_ASSOC);

		$user = new User(array(
			'id' => $row['u_id'],
			'name' => $row['u_name'],
			'admin' => $row['u_admin']
		));

		$row['user'] = $user;

		return new Message($row);
	}

	public function getReadPercent() {
		$q = DB::connection()->prepare(
			'SELECT COUNT(*)::float /
				(SELECT COUNT(*) FROM forum_user WHERE accepted = TRUE)
			FROM forum_thread_read WHERE thread_id = :thread_id'
		);

		$q->execute(array('thread_id' => $this->id));

		return $q->fetchColumn();
	}

	public static function get($id) {
		$q = DB::connection()->prepare(
			'SELECT
				t.id, t.title,
				c.id AS c_id, c.name AS c_name
			FROM forum_thread t
			INNER JOIN forum_category c ON c.id = t.category_id
			WHERE t.id = :id
			GROUP BY t.id, c.id, c.name, t.title'
		);

		$q->execute(array('id' => $id));
		$row = $q->fetch(PDO::FETCH_ASSOC);

		$cat = new Category(array(
			'id' => $row['c_id'],
			'name' => $row['c_name']
		));

		$row['category'] = $cat;

		return new Thread($row);
	}

	private static function createThreads($rows, $settings) {
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
				'read_percent' => $row['t_read_percent']
			));
		}

		if($settings['orderField'] == 'read') {
			if(strtolower($settings['order']) == 'asc') {
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

	public static function search($settings) {
		$userId = BaseController::getLoggedInUser()->id;

		$input = array(
			'limit' => 100,
			'offset' => 0
		);

		$sql = 'WITH ftr AS (SELECT thread_id, COUNT(*)::float /
				(SELECT COUNT(*) FROM forum_user WHERE accepted=TRUE) AS read_percent
				FROM forum_thread_read
				GROUP BY thread_id)';

		switch($settings['orderField']) {
        	case "first":
        		$sql .= "SELECT DISTINCT ON(t.id, m.first_sent)";
        		break;
        	case "last":
        		$sql .= "SELECT DISTINCT ON(t.id, m.last_sent)";
        		break;
        	case "messages":
        		$sql .= "SELECT DISTINCT ON(t.id, t_message_count)";
        		break;
        	case "read":
        		$sql .= "SELECT DISTINCT ON(t.id)";
        		break;
        	default:
        		throw new Exception('Unknown order field (' . $settings['orderField'] . ")");
    	}

        $sql .= " t.id AS t_id, t.title AS t_title, t.category_id AS t_category_id, t.title AS t_title,
        t.message_count AS t_message_count,
        m.first_id AS m_first_id, m.first_sent AT TIME ZONE 'Europe/Helsinki' AS m_first_sent, m.last_id AS m_last_id, m.last_sent AT TIME ZONE 'Europe/Helsinki' AS m_last_sent, m.first_message AS m_first_message, m.last_message as m_last_message,
        uf.id AS u_first_id, ul.id AS u_last_id, uf.name AS u_first_name, ul.name AS u_last_name, uf.admin AS u_first_admin, ul.admin AS u_last_admin, uf.registered AS u_first_registered, ul.registered AS u_last_registered,
        c.name AS c_name,
        ftr.read_percent AS t_read_percent
        FROM forum_thread t
        INNER JOIN (
                SELECT
                        thread_id,
                        first_value(user_id) OVER w AS first_user_id,
                        first_value(id) OVER w AS first_id,
                        first_value(sent) OVER w AS first_sent,
                        first_value(message) OVER w AS first_message,
                        last_value(user_id) OVER w AS last_user_id,
                        last_value(id) OVER w AS last_id,
                        last_value(sent) OVER w AS last_sent,
                        last_value(message) OVER w AS last_message
                FROM forum_message
                WINDOW
                        w AS (PARTITION BY thread_id ORDER BY sent ASC, id ASC ROWS BETWEEN UNBOUNDED PRECEDING AND UNBOUNDED FOLLOWING)
        ) m ON t.id = m.thread_id
        INNER JOIN forum_user uf ON uf.id = m.first_user_id
        INNER JOIN forum_user ul ON ul.id = m.last_user_id
        INNER JOIN forum_category c ON c.id = t.category_id
        LEFT JOIN ftr ON ftr.thread_id = t.id";

        $where = array();

        if($settings['read'] != 'all') {
        	 $sql .= " LEFT JOIN forum_thread_read ftr2 ON ftr2.thread_id = t.id AND ftr2.user_id = :read_user_id\n";
        	 $input['read_user_id'] = $userId;
        	 $where[] = 'ftr2.id IS ' . ($settings['read'] == 'yes' ? 'NOT NULL' : 'NULL');
        }

        if($settings['participated'] != 'all') {
    		$sql .= " LEFT JOIN forum_message m3 ON m3.thread_id = t.id AND m3.user_id = :participated_user_id\n";
			$input['participated_user_id'] = $userId;
    		$where[] = 'm3.thread_id IS ' . ($settings['participated'] == 'yes' ? 'NOT NULL' : 'NULL');
    	}

        if(is_array($settings['category'])) {
        	$where[] = "c.id IN (" . implode(',', $settings['category']) . ")";
        } else if($settings['category'] != 'all') {
        	if(is_numeric($settings['category'])) {
        		$where[] = "c.id = :cat_id";
        		$input['cat_id'] = $settings['category'];
        	} else {
        		$where[] = "c.simplename = :simplename";
        		$input['simplename'] = $settings['category'];
        	}
        }

        switch($settings['time']) {
        	case "day":
        		$where[] = "m.first_sent > NOW() - INTERVAL '1 days'";
        		break;
        	case "week":
        		$where[] = "m.first_sent > NOW() - INTERVAL '1 weeks'";
        		break;
        	case "month":
        		$where[] = "m.first_sent > NOW() - INTERVAL '1 months'";
        		break;
        	case "all":
        		break;
        	default:
        		throw new Exception('Unknown time field (' . $settings['time'] . ")");
        }

        if(count($where) > 0) {
        	$sql .= " WHERE " . implode("\n AND ", $where) . "\n";
        }

        $sql .= " GROUP BY t.id, t.title, t.category_id, t.message_count, m.thread_id, m.first_id, m.last_id, m.first_sent, m.last_sent, c.name, m.first_message, m.last_message, uf.id, ul.id, uf.name, ul.name, uf.admin, ul.admin, uf.registered, ul.registered, ftr.read_percent\n";
        
        switch($settings['orderField']) {
        	case "first":
        		$sql .= " ORDER BY m.first_sent " . $settings['order'];
        		break;
        	case "last":
        		$sql .= " ORDER BY m.last_sent " . $settings['order'];
        		break;
        	case "messages":
        		$sql .= " ORDER BY t_message_count " . $settings['order'];
        		break;
    	}       

       	$sql .= " LIMIT :limit OFFSET :offset";

		$q = DB::connection()->prepare($sql);

		$q->execute($input);

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);
		
		return self::createThreads($rows, $settings);
	}
}

?>