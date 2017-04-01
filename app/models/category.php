<?php

class Category extends BaseModel {
	public $id, $name, $thread_count;

	public function __construct($attributes) {
		parent::__construct($attributes);
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_user (name) VALUES (:name) RETURNING id'
		);

		$q->execute(array(
			'name' => $this->name
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	public static function threadCount() {
		$q = DB::connection()->prepare(
			'SELECT COUNT(*) FROM forum_thread'
		);

		$q->execute();

		return $q->fetchColumn();
	}

	public static function all() {
		$q = DB::connection()->prepare(
			'SELECT c.*, COUNT(t.*) AS thread_count FROM forum_category c LEFT JOIN forum_thread t ON t.category_id = c.id GROUP BY c.id ORDER BY c.id'
		);

		$q->execute();

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);

		$cats = array();
		foreach($rows as $row) {
			$cats[] = new Category($row);
		}

		return $cats;
	}
}

?>