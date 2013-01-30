<?php
namespace devmo\daos;

use \devmo\exceptions\CoreException;
use \devmo\exceptions\InvalidException;

class Database extends \devmo\Dao {
	public static $dbhs = array();
	private $host;
	private $name;
	private $user;
	private $pass;
	private $port;
	private $record;
	private $records;
	private $iterator;

	public function __construct ($host, $user, $pass, $name=null, $port=null) {
		if (!defined('DATABASE_DATE_FORMAT'))
			define('DATABASE_DATE_FORMAT','Y-m-d');
		if (!defined('DATABASE_DATE_FIRST_FORMAT'))
			define('DATABASE_DATE_FIRST_FORMAT','Y-m-01');
		if (!defined('DATABASE_DATETIME_FORMAT'))
			define('DATABASE_DATETIME_FORMAT','Y-m-d H:i:s');
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->name = $name;
		$this->port = $port ? (int) $port : null;
		$this->dbk = "{$this->host}".($this->port?":{$this->port}":'').";{$this->user}".($this->name?";{$this->name}":'');
	}

	protected function connect () {
		if (!($dbh = self::getDbh($this->dbk))) {
			$dbh = new \mysqli($this->host,$this->user,$this->pass,$this->name,$this->port);
			if (!($dbh instanceof \mysqli))
				throw new CoreException('Database',array('error'=>'Could not connect to the database'));
			self::setDbh($this->dbk,$dbh);
			if ($this->name!=null)
				$this->useSchema($this->name);
		}
		return $dbh;
	}

	protected function useSchema ($name) {
		self::getDbh($this->dbk)->select_db($name);
	}

	protected function disconnect () {
		if (self::getDbh($this->dbk))
			self::getDbh($this->dbk)->close();
		return true;
	}

	protected function query ($sql, $add=null, $debug=false) {
		if ($debug)
			self::debug($sql,'Database::query::sql');
		if (!($dbh = self::getDbh($this->dbk)))
			$dbh = $this->connect();
		if (!$result = $dbh->query($sql))
			throw new CoreException('Database',array('errorno'=>$dbh->errno,'error'=>$dbh->error.PHP_EOL.preg_replace('=\s+=',' ',$sql)));
		if ($result instanceof \mysqli_result)
			return new ResultSetDatabaseDao($result);
		if ($add) {
			$id = self::getDbh($this->dbk)->insert_id;
			if ($add instanceof \devmo\Dto)
				$add->setId($id);
			return $id;
		}
		return true;
	}

	protected function formatDate ($date=null, $nullable=true, $first=false) {
		if ($date===null) {
			if (!$nullable)
				throw new InvalidException('Date');
			return 'NULL';
		}
		if ($date instanceof \DateTime)
			return '\''.$date->format(($first?DATABASE_DATE_FIRST_FORMAT:DATABASE_DATE_FORMAT)).'\'';
		if (!($date = strtotime($date)))
				throw new InvalidException('Date');
		return "'".date(($first?DATABASE_DATE_FIRST_FORMAT:DATABASE_DATE_FORMAT),$date)."'";
  }

	protected function formatDateTime ($dateTime=null, $nullable=true) {
		if ($dateTime===null) {
			if (!$nullable)
				throw new InvalidException('DateTime');
			return 'NULL';
		}
		if ($dateTime instanceof \DateTime)
			return '\''.$dateTime->format(DATABASE_DATETIME_FORMAT).'\'';
		if (!($dateTime = strtotime($dateTime)))
				throw new InvalidException('DateTime');
		return "'".date(DATABASE_DATETIME_FORMAT,$dateTime)."'";
  }

	protected function formatNumber ($number=null, $nullable=true) {
		if (($number===null && !$nullable) || ($number!==null && !is_numeric($number)))
			throw new InvalidException('number',$number);
		return $number===null ? 'NULL' : $number;
  }

	protected function formatBoolean ($boolean=null, $nullable=true) {
		if (($boolean===null && !$nullable) || ($boolean!==null && !is_bool($boolean)))
			throw new InvalidException('boolean',$boolean);
		return ($boolean===null ? 'NULL' : ($boolean ? '1' : '0'));
  }

	protected function formatText ($text=null, $nullable=true) {
		if (!$nullable && $text===null)
			throw new InvalidException('text',$text);
		return $text===null ? 'NULL' : "'".self::getDbh($this->dbk)->real_escape_string($text)."'";
  }

	public static function getDbh ($dbhKey) {
		return empty(self::$dbhs[$dbhKey])
			? false
			: self::$dbhs[$dbhKey];
	}

	public static function setDbh ($dbhKey,$dbh) {
		self::$dbhs[$dbhKey] = $dbh;
	}

}


class ResultSetDatabaseDao implements \Iterator, \Countable {
	private $dto = null;
	private $result = null;
	private $position = 0;
	private $count = 0;

	public function __construct ($result) {
		if (!($result instanceof \mysqli_result))
			throw new InvalidException('db query resource',$result);
		$this->result = $result;
		$this->position = 0;
	}

	function __destruct () {
		$this->result->free_result();
	}

	public function setDto ($dto) {
		$this->dto = $dto;
		return $this;
	}

	public function setCount ($count) {
		$this->count = $count;
		return $this;
	}

	function rewind () {
		$this->position = 0;
		if ($this->count()>0)
			$this->result->data_seek($this->position);
	}

	function current () {
		return $this->getObject();
	}

	function key () {
		return $this->position;
	}

	function next () {
		$this->position++;
	}

	function valid () {
		return $this->result->data_seek($this->position);
	}

	public function count () {
		if (!$this->count)
			$this->count = empty($this->result->num_rows) ? 0 : $this->result->num_rows;
		return $this->count;
	}

	function getFields () {
		return $this->result->fetch_fields();
	}

	function getValue () {
		if ($x = $this->result->fetch_array())
			return $x[0];
		return false;
	}

	function getList () {
		$this->rewind();
		$list = array();
		while ($x = $this->result->fetch_array())
			$list[] = $x[0];
		$this->rewind();
		return $list;
	}

	function getHash () {
		$hash = array();
		$this->rewind();
		while ($x = $this->result->fetch_array())
			$hash[$x[0]] = $x[1];
		return $hash;
	}

	function getObject () {
		return ($x = $this->result->fetch_object()) ? $this->dto ? new $this->dto($x) : $x : false;
	}

	function getObjects () {
		$objs = array();
		$this->rewind();
		while ($x = $this->getObject()) {
			$objs[] = $x;
		}
		return $objs;
	}

	function getRow () {
		return ($x = $this->result->fetch_row()) ? $this->dto ? new $this->dto($x) : $x : false;
	}

	function getArray () {
		return ($x = $this->result->fetch_array()) ? $this->dto ? new $this->dto($x) : $x : false;
	}

}
