<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_POST['eintragId'], $_POST['rtb'])){
		echo json_encode(deleteEintrag($db, $_POST['rtb'], $_POST['eintragId'], $username, $userId));
	} else {
		echo 'Direkter Aufruf geblockt !';
	}
?>