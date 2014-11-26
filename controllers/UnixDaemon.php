<?php
namespace bueno\controllers;

use \bueno\excpetions\InvalidException;
use \bueno\Config;

// setup signal handling
declare(ticks=1);
pcntl_signal(SIGTERM,'\bueno\controllers\UnixDaemon::handleSignal');
pcntl_signal(SIGHUP,'\bueno\controllers\UnixDaemon::handleSignal');
pcntl_signal(SIGINT,'\bueno\controllers\UnixDaemon::handleSignal');
pcntl_signal(SIGUSR1,'\bueno\controllers\UnixDaemon::handleSignal');

abstract class UnixDaemon extends \bueno\Controller {
	protected $maxProcesses = 1;
	protected $runInterval = 1;
	
	public static function handleSignal ($signal) {
		$pid = getmypid();
		try {
			switch ($signal) {
				case SIGTERM:
				case SIGHUP:
				case SIGINT:
					self::stop();
				case SIGUSR1:
					self::logError('[INFO] '.__METHOD__.'['.$pid.']::SIGUSR1');
					break;
				default:
					throw new InvalidException('signal',$signal);
			}
		} catch (InvalidException $e) {
			self::logError('[ERROR] '.__METHOD__.'['.$pid.'] '.$e);
		}
	}

	public function run (array $args=null) {
		if (!Config::isCli())
			throw new Exception('Daemon must be CLI');
		if ($this->maxProcesses) {
			exec('ps aux | grep -v grep | grep '.preg_replace('/^.*\\\(\w+)$/','\1',get_class($this)),$processes);
			if (count($processes)>$this->maxProcesses) {
				self::logError('[INFO] '.__METHOD__.'['.getmypid().'] max processes reached:'.$this->maxProcesses);
				self::handleSignal(SIGTERM);
			}
		}
		// daemonize
		$pid = pcntl_fork();
		// only the parent will know it's pid
		if ($pid==-1) {
			self::logError('[ERROR] '.__METHOD__."[{$pid}] failed to fork");
			self::handleSignal(SIGTERM);
		} else if ($pid>0) {
			self::logError('[INFO] '.__METHOD__."[{$pid}] daemonizing");
			self::handleSignal(SIGTERM);
		}
		// detach from terminal
		if (posix_setsid()==-1) {
			self::logError('[ERROR] '.__METHOD__."[{$pid}] failed to detach from terminal");
			self::handleSignal(SIGTERM);
		} else {
			$pid = posix_getpid();
		}
		$args['pid'] = $pid;
		// execute
		while (true) {
			// self::logError('[INFO] '.__METHOD__."[{$pid}] starting");
			$this->runDaemon($args);
			// self::logError('[INFO] '.__METHOD__."[{$pid}] finished");
			sleep($this->runInterval);
		}
	}

	protected static function stop () {
		self::logError('[INFO] '.__METHOD__.'['.getmypid().'] exiting...');
		exit;
	}

	abstract protected function runDaemon (array $args=null);
}
