<?php

class BaseModel{
	protected $validators;

	public function __construct($attributes = null) {
		foreach($attributes as $attribute => $value) {
			if(property_exists($this, $attribute)){
				$this->{$attribute} = $value;
			}
		}
	}

	public function errors() {
		$errors = array();

		foreach($this->validators as $validator) {
			$errors = array_merge($errors, $this->$validator());
		}

		return $errors;
	}
}