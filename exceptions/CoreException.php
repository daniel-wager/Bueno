<?php
namespace bueno\exceptions;
class CoreException extends \bueno\Exception {
	public $name;
	public $tokens;
	public function __construct ($name=null, $tokens=null) {
		$message = "Core::{$name}";
		$this->name = $name;
		if (($this->tokens = is_array($tokens) ? $tokens : array())) {
			$logMessage = "{$message} tokens (";
			foreach ($this->tokens as $k=>$v)
				$logMessage .= (empty($logMessage)?'':', ')."{$k}={$v}";
			$logMessage .= ')';
			$this->setLogMessage($logMessage);
		}
		parent::__construct($message);
	}
}
