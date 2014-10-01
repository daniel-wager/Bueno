<?php
namespace bueno\libs;
use \DateTimeZone;
use \bueno\Config;
class DateTime extends \DateTime {
	private static $localDateTimeZone;
	private static $universalDateTimeZone;
	private $myDateTimeZone; 
	public function __construct ($datetime=null, DateTimeZone $timezone=null) {
		parent::__construct($datetime,$timezone);
		if ($timezone || !Config::getTimeZone())
			self::$localDateTimeZone = $this->getTimezone();
		else if (Config::getTimeZone())
			self::$localDateTimeZone = new DateTimeZone(Config::getTimeZone());
		self::$universalDateTimeZone = new DateTimeZone('UTC');
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