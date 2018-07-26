<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	// Checken, ob es bereits an diesem Datum einen Eintrag  mit der gegebenen Zeit gibt
	if(isset($_POST['rtb'], $_POST['datum'], $_POST['uhrzeit'])){
		$rtbId = getRtbIdFromUrl($db, htmlspecialchars($_POST['rtb']));

		if(checkEntryTime($db, $rtbId, htmlspecialchars($_POST['datum']), htmlspecialchars($_POST['uhrzeit']))) {
			echo 'OK';
		} else {
			echo 'Es ist bereits ein Eintrag mit dieser Uhrzeit vorhanden.';
		}
	} else {
		echo 'Direkter Aufruf geblockt !';
	}
?>