<?php
require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';
	
	$mask = "../users/$username/tmp*_*.*";
	$files = glob($mask);
	
	if(!empty($files)){
		foreach($files as $file){
			$picNumberPos = strlen("../users/$username/tmp");
			$picNumber = (int)substr($file, $picNumberPos, 1);
			$newFilename = substr_replace($file, $picNumber - 1, $picNumberPos, 1);
			rename($file, $newFilename);
		}
	}
	echo json_encode($files);
?>