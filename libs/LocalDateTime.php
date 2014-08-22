<?php
namespace bueno\libs;
use \bueno\libs\DateTime;
use \bueno\Config;
use \DateTimeZone;
class LocalDateTime extends DateTime {
	// public function __construct ($datetime=null, DateTimeZone $timezone=null) {
	public function __construct ($datetime=null) {
		parent::__construct($datetime,new DateTimeZone(Config::getTimeZone()));
	}
}