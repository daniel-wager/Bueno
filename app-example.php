<?php
namespace com\company\project;

// load and use
require_once __DIR__.'/Bueno.php';
use \bueno\Config;

// setup paths
//Config::addNamespace('com.company.project','.project',true);
//Config::addNamespace('com.company.project2','.project2');

// setup controllers
Config::setDefaultController('com.company.project.controllers.home');

// setup routes
//Config::addRequestControllerMapping('=^/blog=','com.company.project.content.controllers.blog');

// debug flag
Config::setDebug(true);

//	do it!
echo \Bueno::run();
