<?php
namespace bueno\controllers;
/**
 * default controller designed to display critical errors
 *
 * @category Framework
 * @author Dan Wager
 * @copyright Copyright (c) 2007
 * @version 1.0
 */
class Error extends \bueno\Controller {
	public $exception;
  public function run (array $args=null) {
		// log it
    $message = "Error:";
		foreach ($args as $k=>$v)
			$message .= " {$k}:{$v}";
    // build wrapper
    $error = $this->getView("bueno.views.{$this->exception->name}Error",$args);
    $view = $this->getView('bueno.views.Error',array('body'=>$error,'trace'=>$this->exception->format('view')));
    $wrap = $this->runController('bueno.SiteWrapper',array('title'=>'Problems!','body'=>$view));
    return $wrap;
  }
	public function setException ($exception) {
		$this->exception = $exception;
		return $this;
	}
}
