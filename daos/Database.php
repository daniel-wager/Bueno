<?php
namespace bueno\daos;

use \bueno\exceptions\CoreException;
use \bueno\exceptions\InvalidException;

class Database extends \bueno\Dao {
	private static $connections = array();
	private $connectionKey;
	private $connectionDsn;
	private $persistant = false;
	private $user;
	private $pass;
	public function __construct ($dsn, $user, $pass, $persistant=false) {
		if (!defined('DATABASE_DATE_FORMAT'))
			define('DATABASE_DATE_FORMAT','Y-m-d');
		if (!defined('DATABASE_DATETIME_FORMAT'))
			define('DATABASE_DATETIME_FORMAT','Y-m-d H:i:s');
		$this->persistant = (bool) $persistant;
		$this->user = $user;
		$this->pass = $pass;
		$this->connectionDsn = $dsn;
		$this->connectionKey = "{$this->connectionDsn}:{$this->user}:{$this->persistant}";
	}
	protected function getPdo () {
		if (!($pdo = self::getValue($this->connectionKey,self::$connections))) {
			try {
				$pdo = new \PDO(
					$this->connectionDsn,
					$this->user,
					$this->pass,
					array(
						\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION,
						\PDO::ATTR_PERSISTENT=>$this->persistant,
						\PDO::ATTR_STATEMENT_CLASS=>array('\bueno\daos\ResultSet'),
						\PDO::ATTR_DEFAULT_FETCH_MODE=>\PDO::FETCH_OBJ));
				//$pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS,array('ResultSet',array($pdo)));
				self::$connections[$this->connectionKey] = $pdo;
			} catch (\PDOException $e) {
				throw new CoreException('Database',array('error'=>"{$e->getMessage()} [{$e->getFile()}:{$e->getLine()}]"));
			}
		}
		return self::$connections[$this->connectionKey];
	}
	protected function useSchema ($name) {
		return $this->getPdo()->exec("USE `{$name}`;");
	}
	protected function disconnect () {
		self::$connections[$this->connectionKey] = null;
		unset(self::$connections[$this->connectionKey]);
	}
	protected function formatText ($text=null) {
		if ($text===null)
			return 'NULL';
		return $this->getPdo()->quote($text);
	}
	protected function formatDate ($date=null, $time=true) {
		if ($date===null)
			return 'NULL';
		if ($date instanceof \DateTime)
			return $date->format(($time?DATABASE_DATETIME_FORMAT:DATABASE_DATE_FORMAT));
		if (!($date = strtotime($date)))
			throw new InvalidException('Date');
		return date(($time?DATABASE_DATETIME_FORMAT:DATABASE_DATE_FORMAT),$date);
  }
	protected function formatNumber ($number=null) {
		if ($number===null)
			return 'NULL';
		if (!is_numeric($number))
			throw new InvalidException('number',$number);
		return $number;
  }
	protected function formatBool ($bool=null) {
		if ($bool===null)
			return 'NULL';
		if (!is_bool($bool))
			throw new InvalidException('bool',$bool);
		return $bool ? 1 : 0;
  }
}

class ResultSet extends \PDOStatement {
	private $position = 0;
	private $count = 0;
	public function setCount ($count) {
		$this->count = $count;
		return $this;
	}
	public function getCount () {
		return $this->count;
	}
	function getValue () {
		return $this->fetchColumn(0);
	}
	function getList () {
		$list = array();
		while (($x = $this->fetchAll(\PDO::FETCH_NUM)))
			$list[] = $x[0];
		return $list;
	}
	function getMap () {
		$list = array();
		while (($x = $this->fetchAll(\PDO::FETCH_NUM)))
			$list[0] = $x[1];
		return $list;
	}
	function getObject () {
		return $this->fetch(\PDO::FETCH_OBJ);
	}
	function getObjects () {
		return $this->fetchAll(\PDO::FETCH_OBJ);
	}
	function getArray ($associative=true, $numeric=false) {
		return $this->fetch(($associative&&$numeric?\PDO::FETCH_BOTH:($associative||!$numeric?\PDO::FETCH_ASSOC:\PDO::FETCH_NUM)));
	}
}
