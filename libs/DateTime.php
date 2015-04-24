<?php
namespace bueno\libs;
use \DateTimeZone;
use \bueno\Config;
use \bueno\exceptions\InvalidException;
class DateTime extends \DateTime {
	private static $utcDtz;
	private static $localDtz;
	protected $myDtz;
	public function __construct ($datetime=null, DateTimeZone $myDtz=null) {
		if ($myDtz!==null && !($myDtz instanceof DateTimeZone))
			throw new InvalidException('myDtz',$myDtz,'DateTimeZone');
		$this->myDtz = $myDtz ?: self::getLocalDtz();
		parent::__construct($datetime,$this->myDtz);
	}
	private static function getUtcDtz () {
		if (!self::$utcDtz)
			self::$utcDtz = new DateTimeZone('UTC');
		return self::$utcDtz;
	}
	private static function getLocalDtz () {
		if (!self::$localDtz)
			self::$localDtz = new DateTimeZone(Config::getTimeZone());
		return self::$localDtz;
	}
	public function switchToLocal () {
		$this->setTimeZone(self::getLocalDtz());
		return $this;
	}
	public function switchToUniversal () {
		$this->setTimeZone(self::getUtcDtz());
		return $this;
	}
	public function switchBack () {
		$this->setTimeZone($this->myDtz);
		return $this;
	}
	public function switchToDateTimeZone (DateTimeZone $dtz) {
		if (!($dtz instanceof DateTimeZone))
			throw new InvalidException('dtz',$dtz,'DateTimeZone');
		$this->setTimeZone($dtz);
		return $this;
	}
	public function format ($format=\DateTime::ATOM) {
		return parent::format($format);
	}
	public function formatLocal ($format=\DateTime::ATOM) {
		$curDtz = $this->getTimeZone();
		$this->switchToLocal();
		$format = $this->format($format);
		$this->setTimeZone($curDtz);
		return $format;
	}
	public function formatUniversal ($format=\DateTime::ATOM) {
		$curDtz = $this->getTimeZone();
		$this->switchToUniversal();
		$format = $this->format($format);
		$this->setTimeZone($curDtz);
		return $format;
	}
	public function formatDateTimeZone ($format=\DateTime::ATOM, DateTimeZone $dtz) {
		if (!($dtz instanceof DateTimeZone))
			throw new InvalidException('dtz',$dtz,'DateTimeZone');
		$curDtz = $this->getTimeZone();
		$this->switchToDateTimeZone($dtz);
		$format = $this->format($format);
		$this->setTimeZone($curDtz);
		return $format;
	}
}
