<?php

class Category extends BaseModel {
	public $id, $name, $simplename, $thread_count, $order;

	public function __construct($attributes) {
		parent::__construct($attributes);
	}

	public function save() {
		$q = DB::connection()->prepare(
			'INSERT INTO forum_category (name) VALUES (:name) RETURNING id'
		);

		$q->execute(array(
			'name' => $this->name
		));

		$row = $q->fetch();

		$this->id = $row['id'];
	}

	public function delete() {
		$q = DB::connection()->prepare(
			'DELETE FROM forum_category WHERE id = :id'
		);

		$q->execute(array('id' => $this->id));
	}

	public static function get($id) {
		$q = DB::connection()->prepare(
			'SELECT * FROM forum_category WHERE id = :id'
		);

		$q->execute(array('id' => $id));

		$row = $q->fetch(PDO::FETCH_ASSOC);

		return new Category($row);
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
			'SELECT c.*, COUNT(t.*) AS thread_count FROM forum_category c LEFT JOIN forum_thread t ON t.category_id = c.id GROUP BY c.id, c.name, c.simplename, c.order ORDER BY c.order'
		);

		$q->execute();

		$rows = $q->fetchAll(PDO::FETCH_ASSOC);

		$cats = array();
		foreach($rows as $row) {
			$cats[] = new Category($row);
		}

		return $cats;
	}

	public static function moveUp($id) {
		$q = DB::connection()->prepare("
			SELECT move_up('forum_category', :id)
		");

		$q->execute(array('id' => $id));
	}

	public static function moveDown($id) {
		$q = DB::connection()->prepare("
			SELECT move_down('forum_category', :id)
		");

		$q->execute(array('id' => $id));
	}
}

?>