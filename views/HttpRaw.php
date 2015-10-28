<?php
if (headers_sent($file,$line))
	throw new Exception("Output already started file:{$file}[{$line}]");
header('HTTP/1.0 '.($this->code ?: '200').($this->message ? " {$this->message}" : ''));
foreach(array_filter(is_array($this->headers) ? $this->headers : array($this->headers)) as $header) {
	header($header);
}
echo $this->content;
