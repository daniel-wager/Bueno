<?php
namespace bueno\exceptions;
use \bueno\Config;
class InvalidException extends \bueno\Exception {
	private $name;
	private $value;
	private $options;
	public function __construct ($name, $value=null, $options=null, $showValueAndOptions=false) {
		$this->setName($name);
		$this->setValue($value);
		$this->setOptions($options);
		$message = ($this->value?'Invalid ':'Missing ').$this->name;
		$this->setLogMessage($message
				.($this->value?"\nValue: ".print_r($this->value,true):'')
				.($this->options?"\nOptions: ".implode('","',$this->options):null));
		parent::__construct(($showValueAndOptions||Config::isDebug()?$this->getLogMessage():$message));
	}
	private function setName ($name) {
		$this->name = trim($name);
	}
	private function setValue ($value) {
		$this->value = $value;
	}
	private function setOptions ($options) {
		$this->options = (is_array($options) ? $options : ($options ? array($options) : null));
	}
	public function getName () {
		return $this->name;
	}
	public function getValue () {
		return $this->value;
	}
	public function getOptions ($toString=false) {
		return $toString && is_array($this->options) ? implode(',',$this->options) : $this->options;
	}
}
