<?php
/**
 * base controller for creating the default wrapper
 *
 * @category Framework
 * @author Dan Wager
 * @copyright Copyright (c) 2007
 * @version 1.0
 */
namespace bueno\controllers;

class SiteWrapper extends \bueno\Controller {

  public function run (array $args=null) {
  	$view = $this->getView('bueno.SiteWrapper',$args);
		if (!$view->title)
			$view->title = "Default!!";
		$view->poweredby = $this->runController('bueno.PoweredBy');
    return $view;
  }

}
