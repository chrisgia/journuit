<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	$error = "";
	try {
		$selector = htmlspecialchars($_POST['selector']);
		$token = htmlspecialchars($_POST['token']);
		if($_POST['passwort'] != $_POST['passwort_confirm']){
			$error = "Die Passwörter stimmen nicht überein.";
		}

		if(empty($error)){
			$auth->resetPassword($selector, $token, $_POST['passwort']);
		}
	}

	catch (\Delight\Auth\InvalidSelectorTokenPairException $e) {
	    $error = "Der Autorisierungstoken ist ungültig.";
	}
	catch (\Delight\Auth\TokenExpiredException $e) {
	    $error = "Der Autorisierungstoken ist abgelaufen.";
	}
	catch (\Delight\Auth\InvalidPasswordException $e) {
	    $error = "Das Passwort ist ungültig.";
	}
	catch (\Delight\Auth\TooManyRequestsException $e) {
	    $error = "Die maximale Anzahl an Anfragen wurde überschritten.";
	}

	if(!empty($error)){
		echo $error;
	} else {
		echo 'OK';
	}
?>