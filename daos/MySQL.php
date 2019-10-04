<?php
namespace bueno\daos;
use \PDO;
use \bueno\exceptions\InvalidException;
class MySQL extends Database {
	public function __construct ($user, $pass, $name=null, $host=null, $port=null, $socket=null, $charset=null, $persistant=false) {
		if (!in_array('mysql',PDO::getAvailableDrivers()))
			throw new InvalidException('db driver','sqlsrv',PDO::getAvailableDrivers());
		if (!defined('DATABASE_ATOM_FORMAT'))
			define('DATABASE_ATOM_FORMAT','%Y-%m-%dT%H:%i:%s+00:00');
		$dsn = "mysql:".($socket?"unix_socket={$socket}":($host?"host={$host}":'').($port?";port={$port}":'')).($name?";dbname={$name}":'').($charset?";charset={$charset}":'');
		parent::__construct($dsn,$user,$pass,$persistant);
	}
	public function getTotalCount () {
		return $this->getPdo()->query("SELECT FOUND_ROWS();")->getValue();
	}
}
