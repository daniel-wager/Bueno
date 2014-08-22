<?php
namespace bueno\libs;
use \bueno\libs\DateTime;
use \DateTimeZone;
class UniversalDateTime extends DateTime {
	// public function __construct ($datetime=null, DateTimeZone $timezone=null) {
	public function __construct ($datetime=null) {
		parent::__construct($datetime,new DateTimeZone('UTC'));
	}
}