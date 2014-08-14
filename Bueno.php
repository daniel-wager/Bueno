<?php
// set framework constants
if (!defined('BUENO_PATH'))
	define('BUENO_PATH',preg_replace('=^(.+)/[^/]+$=','\1',__FILE__));
// require core classes
require_once BUENO_PATH.'/bueno/Core.php';
// uses
use \bueno\Core;
use \bueno\Config;
// class
class Bueno extends \bueno\Object {
	public static function run ($controller=null, $args=null) {
		Config::sortNamespacePathMap();
		return Core::execute(($controller ? Core::formatPath($controller,'controllers') : null),$args)->getRoot();
	}
}
// set default configs
Config::init();
Config::setDev(Bueno::getValue('SERVER_DEV',$_SERVER,false));
Config::setDebug(Config::isDev());
Config::addNamespace('bueno',BUENO_PATH,false);
Config::setRequestBase(Bueno::getValue('SCRIPT_NAME',$_SERVER));
Config::setRequest(Bueno::getValue('PATH_INFO',$_SERVER));
