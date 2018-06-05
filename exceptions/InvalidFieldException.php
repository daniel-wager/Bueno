<?php
namespace bueno\exceptions;
class InvalidFieldException extends InvalidException {
	private $field;
	public function __construct ($field, $name=null, $value=null, $options=null) {
		$this->setField($field);
		parent::__construct(($name?:ucfirst($field)),$value,$options);
	}
	private function setField ($field) {
		$this->field = $field;
	}
	public function getField () {
		return $this->field;
	}
}
