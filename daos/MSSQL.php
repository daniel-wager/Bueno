<?php
namespace bueno\daos;
use \PDO;
use \bueno\exceptions\InvalidException;
class MSSQL extends Database {
	public function __construct ($user, $pass, $name=null, $host=null) {
		if (!in_array('sqlsrv',PDO::getAvailableDrivers()))
			throw new InvalidException('db driver','sqlsrv',PDO::getAvailableDrivers());
		if (!defined('DATABASE_ATOM_FORMAT'))
			define('DATABASE_ATOM_FORMAT','%Y-%m-%dT%H:%i:%s+00:00');
		$dsn = "sqlsrv:Server={$host};Database={$name}";
		parent::__construct($dsn,$user,$pass,false);
	}
	public function getTotalCount () {
		return $this->getPdo()->query("SELECT FOUND_ROWS();")->getValue();
	}
}
