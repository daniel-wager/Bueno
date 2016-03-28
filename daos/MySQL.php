<?php
namespace bueno\daos;
class MySQL extends Database {
	public function __construct ($user, $pass, $name=null, $host=null, $port=null, $socket=null, $charset=null, $persistant=false) {
		if (!defined('DATABASE_ATOM_FORMAT'))
			define('DATABASE_ATOM_FORMAT','%Y-%m-%dT%H:%i:%s+00:00');
		$dsn = "mysql:host={$host}".($port?";port={$port}":'').($name?";dbname={$name}":'').($charset?";charset={$charset}":'');
		parent::__construct($dsn,$user,$pass,$persistant);
	}
	public function getTotalCount () {
		return $this->getPdo()->query("SELECT FOUND_ROWS();")->getValue();
	}
}
