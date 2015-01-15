<?php
namespace bueno\daos;

class MySQL extends Database {
	public function __construct ($user, $pass, $name=null, $host=null, $port=null, $socket=null, $charset=null, $persistant=false) {
		$dsn = "mysql:host={$host}".($port?";port={$port}":'').($name?";dbname={$name}":'').($charset?";charset={$charset}":'');
		parent::__construct($dsn,$user,$pass,$persistant);
	}
}
