<?php
namespace bueno\daos;
use \PDO;
use \PDOException;
use \PDOStatement;
use \Bueno;
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
	protected function getPdo ($new=false) {
		if ($new || !($pdo = self::getValue($this->connectionKey,self::$connections))) {
			try {
				$pdo = new PdoDao(
					$this->connectionDsn,
					$this->user,
					$this->pass,
					array(
						PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION
						,PDO::ATTR_PERSISTENT=>$this->persistant
						,PDO::ATTR_STATEMENT_CLASS=>array('\bueno\daos\ResultSet')
						,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_OBJ));
				if ($new)
					return $pdo;
				self::$connections[$this->connectionKey] = $pdo;
			} catch (PDOException $e) {
				throw new CoreException('Database',array('error'=>"{$e->getMessage()} dsn:{$this->connectionDsn}"));
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
	public function beginTransaction () {
		return $this->getPdo()->beginTransaction();
	}
	public function commitTransaction () {
		return $this->getPdo()->commit();
	}
	public function rollbackTransaction () {
		return $this->getPdo()->rollBack();
	}
	protected function formatText ($text=null) {
		if ($text===null)
			return 'NULL';
		return $this->getPdo()->quote($text);
	}
	protected function formatDate ($date=null, $format=null) {
		if ($date===null)
			return 'NULL';
		if ($date instanceof \DateTime)
			return '\''.$date->format(($format?:DATABASE_DATE_FORMAT)).'\'';
		if (!($date = strtotime($date)))
			throw new InvalidException('Date');
		return '\''.date(($format?:DATABASE_DATE_FORMAT),$date).'\'';
  }
	protected function formatDateTime ($dateTime=null, $format=null) {
		if ($dateTime===null)
			return 'NULL';
		if ($dateTime instanceof \DateTime)
			return '\''.$dateTime->format(($format?:DATABASE_DATETIME_FORMAT)).'\'';
		if (!($dateTime = strtotime($dateTime)))
			throw new InvalidException('Date');
		return '\''.date(($format?:DATABASE_DATETIME_FORMAT),$dateTime).'\'';
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
		if (!(is_bool($bool) || $bool=='yes' || $bool=='no'))
			throw new InvalidException('bool',$bool);
		return $bool===true || $bool=='yes' ? 1 : 0;
  }
}
class PdoDao extends PDO {
	public function query ($sql, $log=false) {
		if ($log)
			Bueno::debug($sql,__METHOD__.'['.__LINE__.']::sql','log');
		return parent::query($sql);
	}
	public function exec ($sql, $log=false) {
		if ($log)
			Bueno::debug($sql,__METHOD__.'['.__LINE__.']::sql','log');
		return parent::exec($sql);
	}
}
class ResultSet extends PDOStatement {
	private $position = 0;
	private $count = 0;
	public function setCount ($count) {
		$this->count = $count;
		return $this;
	}
	public function getCount () {
		return $this->count;
	}
	public function getValue () {
		return $this->fetchColumn(0);
	}
	public function getList () {
		$list = array();
		while (($xs = $this->fetchAll(PDO::FETCH_NUM)))
			foreach ($xs as $x)
				$list[] = $x[0];
		return $list;
	}
	public function getMap () {
		$list = array();
		if (($xs = $this->fetchAll(PDO::FETCH_NUM)))
			foreach ($xs as $x)
				$list[$x[0]] = $x[1];
		return $list;
	}
	public function getObject () {
		return $this->fetch(PDO::FETCH_OBJ);
	}
	public function getObjects () {
		return $this->fetchAll(PDO::FETCH_OBJ);
	}
	public function getArray ($associative=true, $numeric=false) {
		return $this->fetch(($associative&&$numeric?PDO::FETCH_BOTH:($associative||!$numeric?PDO::FETCH_ASSOC:PDO::FETCH_NUM)));
	}
}
