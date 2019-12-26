<?php
namespace bueno\exceptions;
class UniqueException extends \bueno\Exception {
	public function __construct ($name, $value=null, $code=null) {
		$message = "{$name} already exists";
		if (\bueno\Config::isDebug() && $value)
			$message .= ' value:"'.print_r($value,true)."\" {$this->file}:{$this->line}";
		parent::__construct($message,$code);
	}
}
