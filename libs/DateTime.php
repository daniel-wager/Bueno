<?php
namespace bueno\libs;
use \DateTimeZone;
use \bueno\Config;
class DateTime extends \DateTime {
	private static $utcDtz;
	private static $localDtz;
	public function __construct ($datetime=null, DateTimeZone $myDtz=null) {
		if (!self::$utcDtz)
			self::$utcDtz = new DateTimeZone('UTC');
		if (!self::$localDtz)
			self::$localDtz = new DateTimeZone(Config::getTimeZone());
		parent::__construct($datetime,$myDtz);
	}
	public function switchToLocal () {
		$this->setTimeZone(self::$localDtz);
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