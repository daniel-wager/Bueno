<?php
namespace {
	use \bueno\Config;
	use \bueno\Core;
	class Bueno extends \bueno\BuenoClass {
		public static function run ($controller=null, $args=null) {
			Config::sortNamespacePathMap();
			return Core::execute(($controller ? Core::formatPath($controller,'controllers') : null),$args)->getRoot();
		}
	}
}
namespace bueno {
	use \bueno\exceptions\CoreException;
	use \bueno\exceptions\FileNotFoundCoreException;
	use \bueno\exceptions\FileTypeNotFoundCoreException;
	use \bueno\exceptions\InvalidException;

	class Exception extends \LogicException {
		private $path;
		private $info;
		private $user;
		private $logMessage;
		public function __construct ($message, $path=null) {
			parent::__construct($message);
			$this->path = $path;
			$this->extra = null;
		}
		public function getPath () {
			return $this->path;
		}
		public function setInfo ($info) {
			$this->info = $info;
			return $this;
		}
		public function setUser ($user) {
			$this->user = $user;
			return $this;
		}
		public function setLogMessage ($logMessage) {
			$this->logMessage = $logMessage;
			return $this;
		}
		public function getLogMessage () {
			return $this->logMessage ?: $this->getMessage();
		}
		public function format ($type=null) {
			if ($type && $type!='log' && $type!='view')
				throw new InvalidException('type',$type,array('log','view'));
			$isLog = $type=='log';
			$err = '[ERROR] '.get_class($this).': '
					.($isLog ? $this->getLogMessage() : $this->getMessage())
					.($isLog && $this->user ? PHP_EOL."Who: {$this->user}" : null)
					.PHP_EOL."When: ".date('Y-m-d H:m:s')
					.($isLog && $this->path ? PHP_EOL."Path: {$this->path}" : null)
					.($isLog && $this->info ? PHP_EOL."Info: {$this->info}" : null)
					.PHP_EOL."Where: {$this->file}:{$this->line}";
			if ($isLog) {
				foreach ($this->getTrace() as $i=>$x) {
					if (preg_match('/^bueno\\\/',BuenoClass::getValue('class',$x)))
						continue;
					$args = "";
					foreach ($x['args'] as $xa) {
						if (is_array($xa)) {
							$args .= ($args?',':null).'array';
						} else if (is_object($xa)) {
							$args .= ($args?',':null).get_class($xa);
						} else {
							$args .= ($args?',':null).var_export($xa, true);
						}
					}
					$err .= PHP_EOL
							.(isset($x['file'])?"{$x['file']}:{$x['line']}":null)
							.(isset($x['class'])?" {$x['class']}{$x['type']}":null)
							.(isset($x['function'])?$x['function'].'('.$args.') ':null);
				}
			}
			$err .= PHP_EOL;
			return $err;
		}
		public function __toString () {
			return $this->format();
		}
	}

	class BuenoClass {
		public function __toString () {
			return 'BuenoClass:\\'.get_class($this);
		}
		public static function debug ($mixed, $title=null, $options=null) {
			$options = is_array($options) ? $options : explode(',',$options);
			$exit = in_array('exit',$options);
			$buffer = '';
			if (in_array('trace',$options)) {
				foreach (debug_backtrace() as $i=>$x) {
					$args = '';
					if (is_array(self::getValue('args',$x)))
						foreach ($x['args'] as $xarg)
							$args .= ($args ? "," : '').(is_null($xarg) || is_scalar($xarg) ? $xarg : gettype($xarg));
					$buffer .= "{$i} ".($x['function']?(isset($x['class'])?"{$x['class']}::":null)."{$x['function']}({$args}) ":null)."{$x['file']}:{$x['line']}".PHP_EOL;
				}
			}
			if (in_array('xml',$options)) {
				$buffer .= $mixed->asXML();
			} else if (in_array('export',$options)) {
				$buffer .= var_export($mixed,true);
			} else {
				$buffer .= print_r($mixed,true);
			}
			if (in_array('log',$options)) {
				$success = self::logError("[DEBUG] {$title}\n{$buffer}");
				return $exit ? exit : $success;
			}
			$buffer = (Config::isCli() ? null : '<pre class="debug">').PHP_EOL.$title.PHP_EOL.(in_array('textarea',$options)?"<textarea>{$buffer}</textarea>":$buffer).(Config::isCli() ? (in_array('pause',$options) ? null : PHP_EOL.PHP_EOL) : PHP_EOL.'</pre>'.PHP_EOL);
			if (in_array('return',$options)) {
				return $buffer;
			} else {
				print $buffer;
			}
			if (in_array('pause',$options)) {
				Config::isCli() ? fgets(STDIN) : trigger_error("'{$option}' is only used in cli mode",E_USER_WARNING);
			}
			if ($exit) {
				exit;
			}
		}
		public static function setValue ($needle, &$haystack, $value=null) {
			if ($needle===null || !is_scalar($needle))
				throw new InvalidException('needle',$needle,'scalar');
			if (is_null($haystack) || is_scalar($haystack))
				throw new InvalidException('haystack',$haystack,array('array','object'));
			if (is_array($haystack))
				$haystack[$needle] = $value;
			else if (is_object($haystack))
				$haystack->{$needle} = $value;
			return $haystack;
		}
		public static function unsetValue ($needle, &$haystack) {
			if ($needle===null || !is_scalar($needle))
				throw new InvalidException('needle',$needle,'scalar');
			if (is_null($haystack) || is_scalar($haystack))
				throw new InvalidException('haystack',$haystack,array('array','object'));
			if (is_array($haystack))
				unset($haystack[$needle]);
			else if (is_object($haystack))
				unset($haystack->{$needle});
			return $haystack;
		}
		public static function getValue ($needle, $haystack, $default=null, $emptyToDefault=false) {
			if ($needle===null || !is_scalar($needle)) {
				self::debug($needle,__METHOD__.'['.__LINE__.']::'.($needle?'Invalid':'Missing').' needle','log,trace,export');
				throw new InvalidException('needle',$needle,'scalar');
			}
			if ($haystack===null)
				return $default;
			if (is_scalar($haystack)) {
				self::debug($haystack,__METHOD__.'['.__LINE__.']::'.($haystack?'Invalid':'Missing').' haystack','log,trace,export');
				throw new InvalidException('haystack',$haystack,array('array','object','null'));
			}
			$value = is_array($haystack)
					? (isset($haystack[$needle]) ? $haystack[$needle] : $default)
					: (isset($haystack->{$needle}) ? $haystack->{$needle} : $default);
			if (is_string($value))
				$value = trim($value);
			return $emptyToDefault && empty($value) && !is_bool($value) && $value!==0 && $value!=='0' ? $default : $value;
		}
		public static function getPregValue ($pattern, $haystack, $default=null) {
			if ($haystack===null)
				return $default;
			if ($pattern===null || !is_string($pattern))
				throw new InvalidException('pattern',$pattern);
			if (is_array($haystack))
				return ($values = array_intersect_key($haystack,array_flip(preg_grep($pattern,array_keys($haystack))))) ? $values : $default;
			if (is_object($haystack))
				return ($values = array_intersect_key(get_object_vars($haystack),array_flip(preg_grep($pattern,array_keys(get_object_vars($haystack)))))) ? $values : $default;
			throw new InvalidException('haystack',$haystack,array('array','object','null'));
		}
		public static function logError ($message) {
			if ($message instanceof Exception)
				$message = $message->format('log');
			Config::getErrorLog()
				? error_log(date('Y-m-d H:i:s T')." {$message}\n",3,Config::getErrorLog())
				: error_log($message);
		}
	}

	class Factory extends BuenoClass {
		private static $fileBoxes = array();
		public static function build ($path, $option='filebox', $args=null) {
			if (!$path)
				throw new InvalidException('path',$path);
			// allow paths with ns seperator
			if (strstr($path,'\\'))
				$path = str_replace('\\','.',(substr($path,0,1)=='\\'?substr($path,1):$path));
			if (!($fileBox = self::getValue($path,self::$fileBoxes))) {
				// find context and type
				preg_match('/^(.*?)([^\.]+)\.([^\.]+)$/',$path,$matches);
				$context = self::getValue(1,$matches);
				if (!isset($matches[2]) || !($folder = Config::getFolderForType($matches[2])))
					throw new FileTypeNotFoundCoreException(self::getValue(2,$matches),$path);
				$type = $matches[2];
				// put it together
				$fileBox = new FileBox();
				$fileBox->setPath($path);
				$fileBox->setType($type);
				$fileBox->setContext($context);
				if ($option!='filebox')
					$fileBox->setClass(str_replace('.','\\','.'.$matches[1].$matches[2].'.'.ucfirst($matches[3])));
				$xFile = null;
				foreach (Config::getNamespacePathForType($type) as $xNamespace=>$xPath) {
					if (preg_match("/^{$xNamespace}/",$context)>0) {
						$xName = preg_replace('/[ _-]+/','',ucwords($matches[3]));
						$xDir = preg_replace("/^{$xNamespace}/",$xPath,str_replace('.','/',$context)).($xNamespace=='bueno'?$type:$folder);
						$xFile = $xDir.'/'.$xName.'.php';
						if (is_file($xFile)) {
							$fileBox->setFile($xFile);
							break;
						}
						break;
					}
				}
				if ($xFile==null)
					throw new CoreException('NamespaceNotDefined',array('path'=>$path,'namespace'=>$context,'namespaces'=>implode(',',Config::getNamespacesForType($type))));
				self::$fileBoxes[$path] = $fileBox;
			}
			// check for file
			if ($option=='filebox') {
				if (!$fileBox->getFile())
					throw new FileNotFoundCoreException($path,$xFile,Config::getRequest());
				return $fileBox;
			}
			// check for class
			if (!self::classExists($fileBox->getClass())) {
				if (!$fileBox->getFile())
					throw new FileNotFoundCoreException($fileBox->getPath(),$xFile,Config::getRequest());
				require_once($fileBox->getFile());
				if (!self::classExists($fileBox->getClass()))
					throw new CoreException('ClassNotFound',array('class'=>$fileBox->getClass(),'file'=>$fileBox->getFile()));
			}
			// check for parent class
			$parentClass = null;
			switch ($fileBox->getType()) {
				case 'controllers': $parentClass = '\bueno\Controller'; break;
				case 'views': $parentClass = '\bueno\View'; break;
				case 'daos': $parentClass = '\bueno\Dao'; break;
				case 'dtos': $parentClass = '\bueno\Dto'; break;
			}
			if ($parentClass && !self::classExists($parentClass))
				throw new CoreException('ClassNotFound',array('class'=>$parentClass,'file'=>$fileBox->getFile()));
			//  handle options
			$obj = null;
			$class = $fileBox->getClass();
			switch ($option) {
				default:
				case 'auto':
					$obj = in_array('getInstance',get_class_methods($class))
						? call_user_func(array($class,'getInstance'))
						: new $class;
					break;
				case 'static':
					if (!in_array('getInstance',get_class_methods($class)))
						return true;
				case 'singleton':
					$obj = $class::getInstance($args);
					break;
				case 'new':
					$obj = new $class($args);
					break;
			}
			if ($parentClass && !($obj instanceof $parentClass))
				throw new CoreException('ClassNotController',array('class'=>$fileBox->getClass(),'file'=>$fileBox->getFile()));
			if ($obj instanceof Loader)
				$obj->setFileBox($fileBox);
			return $option=='static' ? true : $obj;
		}
		protected static function classExists ($class) {
			return class_exists($class,false) || interface_exists($class,false) || trait_exists($class,false);
		}
	}

	class Core extends BuenoClass {
		public static function execute ($path=false, $args=null, Controller $caller=null, $parentClass=null) {
			// find controller
			if (!($controller = $path) && Config::getRequestedController()) {
				$controller = Config::getRequestedController();
				// find mapped controller
				if (Config::hasRequestControllerMap()) {
					foreach (Config::getRequestControllerMap() as $pattern=>$useController) {
						if (preg_match($pattern,$controller)) {
							$controller = $useController;
							break;
						}
					}
				}
			}
			if (!$controller || $controller === '/')
				$controller = Config::getDefaultController();
			// set up cli args
			if (!$args && !$caller && Config::isCli()) {
				if (($args = array_slice(self::getValue('argv',$_SERVER,array()),2))) {
					foreach ($args as $i=>$x) {
						if (strstr($x,'=')) {
							list($k,$v) = explode('=',$x);
							unset($args[$i]);
							$args[$k] = $v;
						}
					}
				}
			}
			//	get controller view
			if (!$view = self::executeController($controller,$args,null,$caller,$parentClass))
				throw new CoreException('ViewNotFound',array('controller'=>$controller));
			return $view;
		}
		private static function executeController ($path, array $args=null, $message=null, Controller $caller=null, $parentClass=null) {
			//  get controller object
			$controller = Factory::build($path,'new');
			if ($parentClass && !($controller instanceof $parentClass))
				throw new CoreException('ClassNotParentClass',array('class'=>$controller->getInfo('class'),'parent'=>$parentClass, 'file'=>$controller->getFile()));
			if ($message)
				$controller->setMessage($message);
			if ($caller)
				$controller->setCaller($caller);
			//	run controller
			$view = $controller->run($args);
			//  forward to next controller
			if ($controller->getForward())
				return self::executeController($controller->getForward(),$args,$message,$caller);
			// return only views
			return $view instanceof View ? $view : null;
		}
		public static function makeSafe ($value) {
			if (is_array($value)) {
				foreach ($value as $k=>$v)
					$value[$k] = self::makeSafe($v);
				return $value;
			} else if (is_object($value)) {
				foreach ($value as $k=>$v)
					$value->{$k} = self::makeSafe($v);
				return $value;
			} else if (is_null($value) || is_bool($value)) {
				return $value;
			} else {
				return htmlentities(trim($value),ENT_NOQUOTES);
			}
		}
		public static function handleError ($number, $message, $file=null, $line=null, array $context=null) {
			//256	E_USER_ERROR	512	E_USER_WARNING	1024	E_USER_NOTICE
			// log it
			self::logError("Error [{$number}] {$message} file:{$file}:{$line}\n");
			// use default error handler for everything else
			return false;
		}
		public static function handleException (\Throwable $e) {
			// log it
			self::logError($e);
			// show offending query if debug
			if (Config::isDebug() && ($e instanceof \PDOException) && ($sql = self::getValue(0,self::getValue('args',self::getValue(0,$e->getTrace())))))
				self::logError("[ERROR] SQL:\t{$sql}");
			// sanely display exception
			if ($e instanceof CoreException) {
				if (Config::showErrorAsHtml()) {
					if (Config::isDebug()) {
						print Factory::build('bueno.controllers.Error','new')->setException($e)->run($e->tokens);
					} else {
						print self::execute(Config::getRequestNotFoundController())->getRoot();
					}
				} else {
					print Config::isDebug() ? (string)$e : $e->getMessage();
				}
			} else {
				Config::getExceptionController()
						? print Factory::build(Config::getExceptionController(),'new')->run(array('e'=>$e))
						: (Config::isDebug()
							? self::debug($e->__toString(),'log entry')
							: self::debug($e->getMessage(),'See the error log for more details'));
			}
		}
		public static function loadClass ($class) {
			try {
				return Factory::build($class,'static');
			} catch (\Exception $e) {
				return !(($e instanceof FileNotFoundCoreException || $e instanceof FileTypeNotFoundCoreException) && count(spl_autoload_functions())>1)
						? self::handleException($e)
						: false;
			}
		}
		public static function formatPath ($path, $type, $context=null) {
			if (!is_string($path) || !preg_match('/^(?P<path>.*?\.)?(?P<type>[^\.]+\.)?(?P<class>[^\.]+)$/',$path,$parts))
				throw new InvalidException('path',$path);
			$parts += array('path'=>null,'type'=>null,'class'=>null);
			if ($parts['path']==null || $parts['path']=='.') {
				$parts['path'] = $context;
				} else if ($parts['path']=='..') {
				$parts['path'] = preg_replace('/^(.*\.)[^\.]+\.$/','\1',$context);
			} else if (strpos($parts['path'],'.')===0 && $context!==null) {
				$parts['path'] = preg_replace('/\.+/','.',$context.$parts['path']);
			}
			$type .= '.';
			if ($parts['type']!=$type) {
				$parts['path'] .= $parts['type'];
				$parts['type'] = $type;
			}
			return $parts['path'].$parts['type'].$parts['class'];
		}
		public static function formatRequestToPath ($request) {
			return preg_match('=(.*?)/?([^/]+)/?$=',$request,$matches)
					? str_replace('/','.',$matches[1].'.controllers.'.str_replace(' ','',ucwords(preg_replace('/[\-\+]+/',' ',$matches[2]))))
					: false;
		}
		public static function formatControllerToRequest ($controller) {
			return Config::getRequestBase().preg_replace(
					array('/^'.Config::getDefaultNamespace().'/','/controllers?\./','/\.+/','/^([^\/]{1})/'),
					array('','','/','/\1'),
					$controller);
		}
	}

	class Config extends BuenoClass {
		private static $typeFolderMap = array(
				'exceptions'=>'_exceptions',
				'controllers'=>'_controllers',
				'logic'=>'_logic',
				'daos'=>'_daos',
				'dtos'=>'_dtos',
				'views'=>'_views',
				'libs'=>'_libs',
				'includes'=>'_incs');
		private static $typeNamespacePathMap = array(
				'exceptions'=>array(),
				'controllers'=>array(),
				'logic'=>array(),
				'daos'=>array(),
				'dtos'=>array(),
				'views'=>array(),
				'libs'=>array(),
				'includes'=>array());
		private static $requestControllerMap = array();
		private static $requestNotFoundController = 'bueno.controllers.FourOFour';
		private static $requestedController = null;
		private static $defaultController = null;
		private static $defaultNamespace = null;
		private static $exceptionController = null;
		private static $errorLogFile = null;
		private static $requestBase = null;
		private static $timeZone = null;
		private static $request = null;
		private static $requestReturnType = 'html';
		private static $debug = false;
		private static $dev = false;
		private static $stage = false;
		private static $cli = false;
		private static $showErrorAsHtml = true;
		private static $init = false;
		public static function init () {
			if (!self::$init) {
				self::$cli = PHP_SAPI=='cli';
				self::setTimeZone(date_default_timezone_get());
				self::setStage(self::getValue('SERVER_STAGE',$_SERVER,false));
				self::setDev(self::getValue('SERVER_DEV',$_SERVER,self::isStage()));
				self::setDebug(self::isDev());
				self::addNamespace('bueno',__DIR__,false);
				self::setRequestBase(self::getValue('SCRIPT_NAME',$_SERVER));
				self::setRequest((self::isCli()?self::getValue(1,self::getValue('argv',$_SERVER)):self::getValue('PATH_INFO',$_SERVER)));
				self::setShowErrorAsHtml(!self::isCli());
				self::$init = true;
			}
		}
		# for application use
		public static function setShowErrorAsHtml ($showErrorAsHtml=true) {
			if (!is_bool($showErrorAsHtml))
				throw new InvalidException('showErrorAsHtml',$showErrorAsHtml,'bool');
			self::$showErrorAsHtml = $showErrorAsHtml;
		}
		public static function setDefaultNamespace ($namespace) {
			if (empty($namespace))
				throw new InvalidException('namespace',$namespace);
			if (self::$defaultNamespace!=$namespace) {
				if (!isset(self::$typeNamespacePathMap['controllers'][$namespace]))
					throw new InvalidException('namespace',$namespace,array_keys(self::$typeNamespacePathMap['controllers']));
				self::$defaultNamespace = $namespace;
			}
		}
		public static function addNamespace ($namespace, $path, $default=false) {
			foreach (self::$typeNamespacePathMap as $k=>$v)
				self::$typeNamespacePathMap[$k][$namespace] = $path;
			if ($default || (self::$defaultNamespace==null && $namespace!='bueno'))
				self::setDefaultNamespace($namespace);
		}
		public static function getPathForNamespace ($namespace, $type='controllers') {
			if (!isset(self::$typeNamespacePathMap[$type]))
				throw new InvalidException('Path Type',$type);
			return self::$typeNamespacePathMap[$type][$namespace];
		}
		public static function addRequestControllerMapping ($pattern, $controller) {
			self::$requestControllerMap[$pattern] = $controller;
		}
		public static function setDefaultController ($controller) {
			self::$defaultController = Core::formatPath($controller,'controllers');
		}
		public static function setExceptionController ($controller) {
			self::$exceptionController = Core::formatPath($controller,'controllers');
		}
		public static function setRequestNotFoundController ($controller) {
			self::$requestNotFoundController = Core::formatPath($controller,'controllers');
		}
		public static function setRequestBase ($requestBase=null) {
			self::$requestBase = ($x = trim($requestBase,'/')) ? "/{$x}" : null;
		}
		public static function setRequest ($request=null) {
			if ($request && substr_count($request,'.')==1)
				list ($request,self::$requestReturnType) = explode('.',$request);
			if ($request && ($request = preg_replace('=/+=','/',$request)) && $request!='/') {
				self::$request = $request;
				self::$requestedController = Core::formatRequestToPath(preg_replace('=\/\d+$=','',$request));
			}
		}
		public static function setDebug ($debug=false) {
			self::$debug = (bool) $debug;
		}
		public static function setDev ($dev=false) {
			self::$dev = (bool) $dev;
		}
		public static function setStage ($stage=false) {
			self::$stage = (bool) $stage;
		}
		public static function setErrorLog ($file) {
			self::$errorLogFile = $file;
		}
		public static function setTimeZone ($timeZone) {
			self::$timeZone = $timeZone;
		}

		# for framework use
		public static function sortNamespacePathMap () {
			foreach(self::$typeNamespacePathMap as $type=>$namespaces)
				uasort(self::$typeNamespacePathMap[$type],function ($a,$b) { return strlen($b)-strlen($a); });
		}
		public static function getRequestBase () {
			return self::$requestBase;
		}
		public static function getRequest () {
			return self::$request;
		}
		public static function getRequestReturnType () {
			return self::$requestReturnType;
		}
		public static function getRequestedController () {
			return self::$requestedController ? self::getDefaultNamespace().self::$requestedController : null;
		}
		public static function getDefaultController () {
			return self::$defaultController;
		}
		public static function getExceptionController () {
			return self::$exceptionController;
		}
		public static function getRequestNotFoundController () {
			return self::$requestNotFoundController;
		}
		public static function hasRequestControllerMap () {
			return (self::$requestControllerMap);
		}
		public static function getRequestControllerMap () {
			return self::$requestControllerMap;
		}
		public static function getTypes () {
			return array_keys(self::$typeFolderMap);
		}
		public static function getFolderForType ($type) {
			return self::getValue($type,self::$typeFolderMap);
		}
		public static function getNamespacesForType ($type) {
			return array_keys(self::getNamespacePathForType($type));
		}
		public static function getNamespacePathForType ($type) {
			return self::getValue($type,self::$typeNamespacePathMap);
		}
		public static function getErrorLog () {
			return self::$errorLogFile;
		}
		public static function isDebug () {
			return self::$debug;
		}
		public static function isDev () {
			return self::$dev;
		}
		public static function isStage () {
			return self::$stage;
		}
		public static function isCli () {
			return self::$cli;
		}
		public static function getDefaultNamespace () {
			if (!self::$defaultNamespace)
				throw new CoreException('DefaultNamespaceNotDefined');
			return self::$defaultNamespace;
		}
		public static function getTimeZone () {
			return self::$timeZone;
		}
		public static function showErrorAsHtml () {
			return self::$showErrorAsHtml;
		}
	}

	class Box extends BuenoClass implements \JsonSerializable {
		public function __construct ($properties=null) {
			if ($properties) {
				if (!is_array($properties) && !is_object($properties))
					throw new InvalidException('properties',$properties,array('object','array'));
				foreach ($this as $k=>$v)
					if (($v = self::getValue($k,$properties)))
						$this->__set($k,$v);
			}
		}
		public function __set ($name, $value=null) {
			if (empty($name) || !is_string($name))
				throw new InvalidException('name',$name,'type string');
			if (!($method = 'set'.ucfirst($name)) || !method_exists($this,$method))
				throw new InvalidException('method',$method,preg_replace(array('/^,+/','/,+/'),array('',','),implode(',',array_map(function($x){ return preg_match('/^set/',$x) ? $x : null; },get_class_methods($this)))));
			return $this->{$method}($value);
		}
		public function __get ($name) {
			if (empty($name) || !is_string($name))
				throw new InvalidException('name',$name,'type string');
			if (!($method = 'get'.ucfirst($name)) || !method_exists($this,$method))
				throw new InvalidException('method',$method,preg_replace(array('/^,+/','/,+/'),array('',','),implode(',',array_map(function($x){ return preg_match('/^get/',$x) ? $x : null; },get_class_methods($this)))));
			return $this->{$method}();
		}
		public function __toString () {
			return print_r($this,true);
		}
		public function jsonSerialize () {
			return get_object_vars($this);
		}
	}

	class FileBox extends Box {
		private $type;
		private $class;
		private $file;
		private $context;
		private $path;
		public function setType ($type) {
			$this->type = $type;
		}
		public function getType () {
			return $this->type;
		}
		public function setClass ($class) {
			$this->class = $class;
		}
		public function getClass () {
			return $this->class;
		}
		public function setFile ($file) {
			$this->file = $file;
		}
		public function getFile () {
			return $this->file;
		}
		public function setContext ($context) {
			$this->context = $context;
		}
		public function getContext () {
			return $this->context;
		}
		public function setPath ($path) {
			$this->path = $path;
		}
		public function getPath () {
			return $this->path;
		}
		public function copy (FileBox $fb) {
			$this->class = $fb->getClass();
			$this->context = $fb->getContext();
			$this->file = $fb->getFile();
			$this->path = $fb->getPath();
			$this->type = $fb->getType();
		}
	}

	class Loader extends BuenoClass {
		protected $fileBox = null;
		protected function build ($path, $args=null, $option='auto') {
			return Factory::build(($path && substr($path,0,1)=='.' ? $this->fileBox->getContext().substr($path,1) : $path),$option,$args);
		}
		protected function exists ($path) {
			return Factory::build(($path && substr($path,0,1)=='.' ? $this->fileBox->getContext().substr($path,1) : $path),'check');
		}
		public function setFileBox (FileBox $fileBox) {
			$this->fileBox = $fileBox;
		}
		public function getInfo ($type=null) {
			return $this->fileBox->{"get".ucfirst($type)}();
		}
	}

	trait SuperGlobals {
		protected static function getGet ($name, $default=null, $emptyToDefault=true, $makeSafe=true) {
			return (($value = self::getValue($name,$_GET,$default,$emptyToDefault)) && $makeSafe && $value!=$default)
				? Core::makeSafe($value)
				: $value;
		}
		protected static function getPost ($name, $default=null, $emptyToDefault=true, $makeSafe=true) {
			return (($value = self::getValue($name,$_POST,$default,$emptyToDefault)) && $makeSafe && $value!=$default)
				? Core::makeSafe($value)
				: $value;
		}
		protected static function getRequest ($name, $default=null, $emptyToDefault=true, $makeSafe=true) {
			return (($value = self::getValue($name,$_REQUEST,$default,$emptyToDefault)) && $makeSafe && $value!=$default)
				? Core::makeSafe($value)
				: $value;
		}
		protected static function getSession ($name, $default=null, $autoStart=false) {
			if (!session_id() && (!$autoStart || !session_start()))
				throw new InvalidException('Session',session_id());
			return self::getValue($name,$_SESSION,$default);
		}
		protected static function getServer ($name, $default=null) {
			return self::getValue($name,$_SERVER,$default);
		}
		protected static function getCookie ($name, $default=null) {
			return self::getValue($name,$_COOKIE,$default);
		}
	}

	abstract class Controller extends Loader {
		//	TODO	forkController ($controller, $args=null, $parentClass=null) // fork process
		use \bueno\SuperGlobals;
		private $forward = null;
		private $message = null;
		private $caller = null;
		public function setForward ($controller) {
			$this->forward = Core::formatPath($controller,'controllers',$this->fileBox->getContext());
		}
		public function getForward () {
			return $this->forward;
		}
		public function setMessage ($message) {
			$this->message = $message;
		}
		public function getMessage () {
			return $this->message;
		}
		public function setCaller ($caller) {
			$this->caller = $caller;
		}
		public function getCaller () {
			return $this->caller;
		}
		public function getReturnType () {
			return Config::getRequestReturnType();
		}
		protected function getView ($path=null, $tokens=null) {
			return new View(Core::formatPath(($path?:basename(str_replace('\\','/',$this->fileBox->getClass()))),'views',$this->fileBox->getContext()),$tokens);
		}
		protected static function getRequestController () {
			return Config::getRequestedController() ?: Config::getDefaultController();
		}
		protected function runController ($controller, $args=null, $parentClass=null) {
			return Core::execute(Core::formatPath($controller,'controllers',$this->fileBox->getContext()),$args,$this,$parentClass);
		}
		protected function runRequest ($request, $args=null) {
			return Core::execute(Config::getDefaultNamespace().Core::formatRequestToPath($request),$args,$this);
		}
		protected function formatRequest ($controller=null, array $get=null, $returnType=null) {
			return $this->formatUrl($controller,$get,$returnType);
		}
		protected function formatUrl ($controller=null, array $get=null, $returnType=null) {
			$request = $controller===null
					? Core::formatControllerToRequest($this->fileBox->getPath())
					: Core::formatControllerToRequest(Core::formatPath($controller,'controller',$this->fileBox->getContext()));
			if ($returnType)
				$request .= '.'.strtolower($returnType);
			if ($get)
				$request .= '?'.http_build_query($get);
			return $request;
		}
		protected function getRedirect ($url, $code = 302) {
			return $this->getView('bueno.HttpRaw', array(
				'code' => $code,
				'headers' => array("Location: {$url}")
			));
		}
		abstract public function run (array $args=null);
	}

	class View extends BuenoClass {
		private $myPath = null;
		private $myParent = null;
		private $myTokens = null;
		public function __construct ($path, $tokens=null) {
			$this->myPath = $path;
			if (!$tokens) {
				$this->myTokens = new \stdClass;
			} else if (is_array($tokens)) {
				$this->myTokens = (object) $tokens;
			} else if (is_string($tokens)) {
				$this->myTokens = (object) array('echo'=>$tokens);
			} else if (is_object($tokens)) {
				$this->myTokens = $tokens;
			}
		}
		public function __set ($name, $value) {
			if ($value===$this)
				throw new InvalidException('Token Value Is Circular Reference',$value);
			if (is_object($value) && $value instanceof View)
				$value->parent = $this;
			$this->myTokens->{$name} = $value;
		}
		public function __get ($name) {
			return self::getValue($name,$this->myTokens);
		}
		public function __toString () {
			ob_start();
			try {
				require(Factory::build($this->myPath,'filebox')->getFile());
			} catch (\Exception $e) {
				Core::handleException($e);
			}
			$x = ob_get_contents();
			ob_end_clean();
			return $x;
		}
		public function getFilePath () {
			return $this->myPath;
		}
		public function getRoot () {
			return $this->myParent
				? $this->myParent->getRoot()
				: $this;
		}
		public function setTokens ($tokens) {
			if (is_array($tokens) || is_object($tokens))
				$this->myTokens = $tokens;
		}
		public function getTokens () {
			return $this->myTokens;
		}
		public function getTokenValue ($needle, $default=null) {
			return parent::getValue($needle,$this->getTokens(),$default);
		}
		public function getPregTokenValue ($needle, $default=null) {
			return parent::getPregValue($needle,$this->getTokens(),$default);
		}
	}

	abstract class Logic extends Loader {
	}

	abstract class Library extends BuenoClass {
	}

	abstract class Dao extends BuenoClass {
	}

	abstract class Dto extends Box {
		public function __construct ($record=null, $validate=true) {
			if ($record) {
				if ($validate) {
					parent::__construct($record);
				} else {
					if (!is_object($record) && !is_array($record))
						throw new InvalidException('record type',$record,array('array','object'));
					foreach ($this as $k=>$v)
						$this->{$k} = self::getValue($k,$record);
				}
			}
		}
	}


	// initializations
	// check for magic quotes
	if (get_magic_quotes_gpc())
		die("Magic Quotes Config is On... exiting.");
	// set default exception handler
	set_error_handler(array('\bueno\Core','handleError'));
	set_exception_handler(array('\bueno\Core','handleException'));
	spl_autoload_register(array('\bueno\Core','loadClass'),true);
	Config::init();
}
