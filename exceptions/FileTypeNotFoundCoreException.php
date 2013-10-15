<?php
namespace bueno\exceptions;
use \bueno\Config;
class FileTypeNotFoundCoreException extends \bueno\exceptions\CoreException {
  public function __construct ($type, $path) {
    parent::__construct('FileTypeNotFound',array('type'=>$type,'path'=>$path,'types'=>implode(',',Config::getTypes())));
  }
}
