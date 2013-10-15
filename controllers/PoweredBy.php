<?php
/**
 * official powered by view controller
 *
 * @category Framework
 * @author Dan Wager
 * @copyright Copyright (c) 2007
 * @version 1.0
 */
namespace bueno\controllers;

class PoweredBy extends \bueno\controllers\Controller {

  public function run (array $args=null) {
    return $this->getView('bueno.PoweredBy');
  }

}
