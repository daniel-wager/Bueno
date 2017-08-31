<?php
namespace bueno\exceptions;
class InvalidFormException extends \bueno\Exception {
	private $exceptions;
	public function __construct (array $exceptions=null) {
		$this->exceptions = array();
		if ($exceptions)
			foreach ($exceptions as $e)
				$this->addFieldException($e);
		parent::__construct('Invalid Form');
	}
	public function mapFieldException ($key, InvalidFieldException $e=null, $useArray=false) {
		if (empty($key) || !is_scalar($key))
			throw new InvalidException('key',$key,'scalar');
		if ($e && !($e instanceof InvalidFieldException))
			throw new InvalidException('exception class',get_class($e),get_class(new InvalidFieldException(null)));
		if (!is_bool($useArray))
			throw new InvalidException('useArray',$useArray,'bool');
		if ($useArray && !empty($this->exceptions[$key])) {
			is_array($this->exceptions[$key])
				? $this->exceptions[$key][] = $e
				: $this->exceptions[$key] = array($this->exceptions[$key],$e);
		} else {
			$this->exceptions[$key] = $e;
		}
	}
	public function addFieldException (InvalidFieldException $e, $useArray=false) {
		$this->mapFieldException($e->getField(),$e,$useArray);
	}
	public function hasFieldException ($field) {
		if (empty($field) || !is_scalar($field))
			throw new InvalidException('field',$field);
		return !empty($this->exceptions[$field]);
	}
	public function hasFieldExceptions () {
		return count($this->exceptions)>0;
	}
	public function getFieldExceptions () {
		return $this->exceptions;
	}
	public function getFieldException ($key) {
		if (empty($key) || !is_scalar($key))
			throw new InvalidException('key',$key);
		return self::getValue($key,$this->exceptions);
	}
	public function getFieldMessages () {
		$messages = array();
		foreach ($this->exceptions as $e) {
			if (is_array($e)) {
				foreach ($e as $ex)
					$messages[] = $ex->getMessage();
			} else {
				$messages[] = $e->getMessage();
			}
		}
		return $messages;
	}
}
