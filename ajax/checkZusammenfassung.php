<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	// Checken, ob es bereits an diesem Datum eine Zusammenfassung gibt
	if(isset($_POST['rtb'], $_POST['datum'])){
		$rtbId = getRtbIdFromUrl($db, htmlspecialchars($_POST['rtb']));

		if(!checkZusammenfassung($db, $rtbId, $_POST['datum'])) {
			echo 'OK';
		} else {
			echo 'Sie haben für dieses Datum bereits eine Zusammenfassung geschrieben.';
		}
	} else {
		echo 'Direkter Aufruf geblockt !';
	}
?>