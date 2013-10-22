<?php
namespace com\uflic\api;

// load and use
require_once '.bueno/Bueno.php';
use \bueno\Config;

// setup paths
Config::addNamespace('com.company.www','.www',true);
//Config::addNamespace('com.company.projectA','.projectA');

// setup controllers
Config::setDefaultController('com.company.www.controllers.home');

// setup routes
//Config::addRequestControllerMapping('=^/blog=','com.company.www.content.controllers.blog');

// debug flag
Config::setDebug(true);

//	do it!
echo \Bueno::run();
