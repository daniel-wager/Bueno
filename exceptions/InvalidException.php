<?php
namespace bueno\exceptions;
use \bueno\Config;
use \bueno\Exception;
class InvalidException extends Exception {
	private $name;
	private $value;
	private $options;
	public function __construct ($name, $value=null, $options=null, $showValueAndOptions=false) {
		$this->setName($name);
		$this->setValue($value);
		$this->setOptions($options);
		$message = (empty($this->value) && !is_bool($this->value) && !is_numeric($this->value) ? 'Missing' : 'Invalid').' '.$this->name;
		$this->setLogMessage($message
				.($this->value?" value:".print_r($this->value,true):'')
				.($this->options?" options:".implode(',',$this->options):null));
		parent::__construct(($showValueAndOptions||Config::isDebug()?$this->getLogMessage():$message));
	}
	public function setName ($name) {
		if (!is_scalar($name))
			throw new InvalidException('name',$name,'scalar');
		$this->name = trim($name);
	}
	public function setValue ($value) {
		$this->value = $value;
	}
	public function setOptions ($options) {
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
