<?php

class Category extends BaseModel {
	public $id, $name;

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

	public static function all() {
		$q = DB::connection()->prepare(
			'SELECT * FROM forum_category'
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