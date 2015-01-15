<?php
namespace bueno\exceptions;
class Exception extends \LogicException {
	private $path;
	private $info;
	public function __construct ($text, $path=null) {
		parent::__construct($text);
		$this->path = $path;
		$this->extra = null;
	}
	public function getPath () {
		return $this->path;
	}
	public function setInfo ($info) {
		$this->info = $info;
	}
	public function __toString () {
		$err = "What: ".$this->getMessage()
				.PHP_EOL."When: ".date('Y-m-d H:m:s')
				.($this->path ? PHP_EOL."Path: {$this->path}" : null)
				.($this->info ? PHP_EOL."Info: {$this->info}" : null)
				.PHP_EOL."Where: {$this->file}:{$this->line}";
		foreach ($this->getTrace() as $i=>$x) {
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
		$err .= PHP_EOL;
		return $err;
	}
	public function __toViewString () {
		$err = "What: ".$this->getMessage()
				.PHP_EOL."When: ".date('Y-m-d H:m:s')
				.($this->path ? PHP_EOL."Path: {$this->path}" : null)
				.($this->info ? PHP_EOL."Info: {$this->info}" : null)
				.PHP_EOL."Where: ";
		$buenoPath = Config::getPathForNamespace('bueno');
		$trace = $this->getTrace();
		foreach ($trace as $i=>$x) {
			if (Object::getValue('class',$x)=='bueno\Core')
				continue;
			$args = array();
			foreach ($x['args'] as $xa) {
				if (is_array($xa)) {
					$args[] = 'Array';
				} else if (is_object($xa)) {
					$args[] = 'Object';
				} else {
					$args[] = $xa;
				}
				$err .= (isset($x['file'])?"{$x['file']}:{$x['line']}":null)
						 .(isset($x['class'])?" {$x['class']}{$x['type']}":null)
						 .(isset($x['function'])?" {$x['function']}(".implode(', ',$args).')':null)
						 .PHP_EOL;
			}
		}
		return $err;
	}
}