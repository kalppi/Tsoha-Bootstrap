<?php

class UrlGenerator {
	private $default = array();

	public function __construct($default) {
		$this->default = $default;
	}

	public function generate($changes = array()) {
		$values = array_merge($this->default, $changes);

		$data = array();

		foreach($changes as $key => $value) {
			if(!isset($this->default[$key]) || $changes[$key] != $this->default[$key]) {
				$data[] = $key . "=" . $value;
			}
		}

		return implode("&", $data);
	}
}

?>