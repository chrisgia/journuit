<?php
	// Entfernt die Bilder die der Benutzer hochgeladen, aber im Endeffekt nicht benutzt hat
	if(isset($_POST['createdFiles'], $_POST['username']) && !empty($_POST['createdFiles']) && !empty($_POST['username'])){
		$picturesToRemove = json_decode($_POST['createdFiles']);
		$username = $_POST['username'];
		// Entfernt die letzte Datei von dem Array, da es das Bild ist, wofür sich der Benutzer entschieden hat
		array_pop($picturesToRemove);
		foreach($picturesToRemove as $index => $file){
			$path = "../users/$username/$file";
			if(file_exists($path)){
				unlink($path);
			}
		}
	}
?>