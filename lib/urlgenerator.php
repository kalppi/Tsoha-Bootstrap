<?php

class UrlGenerator {
	private $default = array();

	public function __construct($default) {
		$this->default = $default;
	}

	public function generate($changes = array()) {
		$values = array_merge($this->default, $changes);

		$url = '';
		$data = array();

		if(isset($changes['category'])) {
			if(!isset($this->default['category']) || $changes['category'] != $this->default['category']) {
				$url .= 'alue/' . $changes['category'];
				unset($changes['category']);
			}
		}

		foreach($changes as $key => $value) {
			if(!isset($this->default[$key]) || $changes[$key] != $this->default[$key]) {
				$data[] = $key . "=" . $value;
			}
		}

		return $url . (count($data) > 0 ? '?' . implode("&", $data) : '');
	}
}

?>