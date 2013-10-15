<?php
namespace bueno\exceptions;
class FileNotFoundCoreException extends \bueno\exceptions\CoreException {
  public function __construct ($path, $file=null, $request=null) {
    parent::__construct('FileNotFound',array('path'=>$path,'file'=>$file,'request'=>$request));
  }
}
