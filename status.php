<?php
set_time_limit(0);

while (true){
	sleep(30);
	$line = trim(file_get_contents('status.txt'));
	$splits = preg_split("/[\t:]+/", $line);
	if (count($splits) == 4){
		$ok = trim($splits[1]);
		$all = trim($splits[3]);
		if ($ok == $all)
			break;
	}
	echo $line."\n";
}

