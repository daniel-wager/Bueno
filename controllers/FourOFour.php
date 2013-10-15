<?php
/**
 * Default Message for handling a file not found.
 *
 * @category Framework
 * @author Dan Wager
 * @copyright Copyright (c) 2007
 * @version 1.0
 */
namespace bueno\controllers;
class FourOFour extends \bueno\controllers\Controller {

  public function run (array $args=null) {
  	header("HTTP/1.0 404 Not Found");
    $view = $this->getView('SiteWrapper');
    $view->body = $this->getView();
    return $view;
  }

}
