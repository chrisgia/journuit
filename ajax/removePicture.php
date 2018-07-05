<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';
	if(isset($_POST['picture'])){
		$result = removePicture($db, $_POST['picture'], $username, $userId);
		if($result['status'] == 'OK'){
			$pictureDiv = '';
			// Ordnen der tmp Bilder
			$mask = "../users/$username/tmp*_*.*";
			$files = glob($mask);
			
			if(!empty($files)){
				foreach($files as $file){
					$picNumberPos = strlen("../users/$username/tmp");
					$picNumber = ((int) substr($file, $picNumberPos, 1));
					$newPicNumber = $picNumber - 1;
					$newFilename = substr_replace($file, $newPicNumber, $picNumberPos, 1);
					rename($file, $newFilename);
					$pictureDiv .= '<div class="uk-animation-fade uk-inline uk-dark" id="picture'.$newPicNumber.'Div"><button class="uk-position-top-right uk-icon-button deletePicture" type="button" uk-icon="icon: close"></button><img class="uk-border-rounded eintragBild" data-src="'.$newFilename.'" src="'.$newFilename.'" uk-img></div>';
				}
			}

			$result['pictureDiv'] = $pictureDiv;
		}
		echo json_encode($result);
	}
?>