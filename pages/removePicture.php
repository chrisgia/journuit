<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_POST['picture'])){
		echo json_encode(removePicture($db, $_POST['picture'], $username, $userId));
	}
?>