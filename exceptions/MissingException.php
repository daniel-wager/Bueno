<?php
namespace bueno\exceptions;
class MissingException extends \bueno\Exception {
  public function __construct ($name, $value=null, $code=null) {
		$message = "{$name} does not exists";
		if (\bueno\Config::isDebug() && $value)
			$message .= ' value:"'.print_r($value,true)."\" {$this->file}:{$this->line}";
    parent::__construct($message,$code);
  }
}
