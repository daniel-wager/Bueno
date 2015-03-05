<?php
namespace bueno\exceptions;
class CoreException extends \bueno\Exception {
	public $name;
	public $tokens;
	public function __construct ($name=null, $tokens=null) {
		$message = "Core::{$name}";
		$this->name = $name;
		if (($this->tokens = is_array($tokens) ? $tokens : array())) {
			$tokens = '';
			foreach ($this->tokens as $k=>$v)
				$tokens .= (empty($tokens)?'':', ')."{$k}={$v}";
			$this->setLogMessage("{$message} ({$tokens})");
		}
		parent::__construct($message);
	}
}
