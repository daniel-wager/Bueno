<?php
namespace bueno\exceptions;

class UniqueException extends \bueno\exceptions\Exception {
  public function __construct ($name, $value=null) {
		$message = "{$name} already exists";
		if (\bueno\Config::isDebug() && $value)
			$message .= ' value:"'.print_r($value,true)."\" {$this->file}:{$this->line}";
    parent::__construct($message);
  }
}
