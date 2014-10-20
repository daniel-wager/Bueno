<?php
header('HTTP/1.0 200 OK');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.($this->fileName?:'fileX.csv'));
$fh = fopen('php://output','w');
if ($this->csvHeaders)
	fputcsv($fh,$this->csvHeaders);
if ($this->csvData)
	foreach ($this->csvData as $x)
		fputcsv($fh,(array)$x);
fclose($fh);