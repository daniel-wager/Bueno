<?php
header('HTTP/1.0 '.($this->code ?: '200').($this->message ? " {$this->message}" : ''));
foreach(is_array($this->headers) ?: array($this->headers) as $header) {
	if (!$header)
		continue;
	header($header);
}
echo $this->content;
