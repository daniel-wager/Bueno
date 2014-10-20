<?php
namespace bueno\libs;
use \DateTimeZone;
use \bueno\Config;
class DateTime extends \DateTime {
	private static $utcDtz;
	private $myDtz; 
	public function __construct ($datetime=null, DateTimeZone $myDtz=null) {
		if (!($this->myDtz = $myDtz))
			$this->myDtz = new DateTimeZone(Config::getTimeZone());
		if (!self::$utcDtz)
			self::$utcDtz = new DateTimeZone('UTC');
		parent::__construct($datetime,$this->myDtz);
	}
	public function switchToLocal () {
		$this->setTimeZone($this->myDtz);
		return $this;
	}
	public function switchToUniversal () {
		$this->setTimeZone(self::$utcDtz);
		return $this;
	}
	public function formatLocal ($format) {
		$this->switchToLocal();
		return parent::format($format);
	}
	public function formatUniversal ($format) {
		$this->switchToUniversal();
		return parent::format($format);
	}
}