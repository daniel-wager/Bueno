<?php
/**
 * Default base controller for creating the wrapper around errors
 *
 * @category Framework
 * @author Dan Wager
 * @copyright Copyright (c) 2007 Devmo
 * @version 1.0
 */
namespace Devmo\controllers;
class ErrorController extends \Devmo\controllers\Controller {
	public $template;

  public function run () {
		// log it
    $message = "Error:";
		foreach ($this->getData() as $k=>$v)
			$message .= " {$k}:{$v}";
    \Devmo\libs\Logger::add($message);
    // build wrapper
    $error = $this->getView("{$this->template}Error",$this->getData());
    $view = $this->getView('/Error',array('body'=>$error));
    $wrap = $this->runController('/SiteWrapper',array('body'=>$view));
    return $wrap;
  }

}