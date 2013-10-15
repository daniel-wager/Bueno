<?php
namespace com\company\project;
require_once '.bueno/Bueno.php';
// uses
use \bueno\Config;
// setup paths
Config::addNamespacePathMapping('com.company.project','.app');
Config::setDefaultNamespace('com.company.project');
// setup controllers
Config::setDefaultController('Home');
//Config::setRequestNotFoundController('RequestNotFound');
//Config::setRequest(Bueno::getValue('PATH_INFO',$_SERVER));
//Config::setDebug(true);
//	do it!
echo \Bueno::run();
