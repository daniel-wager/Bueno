<?php
namespace bueno\libs;
use \DateTimeZone;
use \bueno\Config;
class DateTime extends \DateTime {
	private static $localDateTimeZone;
	private static $universalDateTimeZone;
	private $myDateTimeZone; 
	public function __construct ($datetime=null, DateTimeZone $timezone=null) {
		if (!self::$localDateTimeZone)
			self::$localDateTimeZone = new DateTimeZone(Config::getTimeZone());
		if (!self::$universalDateTimeZone)
			self::$universalDateTimeZone = new DateTimeZone('UTC');
		parent::__construct($datetime,$timezone);
	}
	public function switchToLocal () {
		$this->setTimeZone(self::$localDateTimeZone);
	}
	public function switchToUniversal () {
		$this->setTimeZone(self::$universalDateTimeZone);
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