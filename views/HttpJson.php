<?php
header('HTTP/1.0 200 OK');
header('Content-Type: application/json; charset=utf-8');
echo json_encode($this->myTokens);