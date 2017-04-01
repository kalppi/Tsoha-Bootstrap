<?php

class UrlGenerator {
	private $default = array();
	private $format = "";

	public function __construct($default, $format) {
		$this->default = $default;
		$this->format = $format;
	}

	public function generate($changes = array()) {
		$values = array_merge($this->default, $changes);

		return preg_replace_callback("#:([a-zA-Z]+)#", function($input) use($values) {
			return $values[$input[1]];
		}, $this->format);
	}
}

?>