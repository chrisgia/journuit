<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php';
	if(!($auth->isLoggedIn())) {
		header('Location: /pages/unpermitted.php');
	}
?>