<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_POST['picture'])){
		$file = htmlspecialchars($_POST['picture']);
		$filename = basename($file);
		$fullPath = "../users/$username/$filename";

		// Dateierweiterung von dem Dateinamen entfernen um nur die ID des Bildes zu bekommen
		$dotPos = strrpos($filename, ".");
		$pictureId = substr($filename, 0, $dotPos);

		if (strpos($filename, 'tmp') !== false) {
			// Sicherstellen, dass der Benutzer auf seine eigene Datei zugreifft
			if(strpos($fullPath, '/'.$username.'/')){
				$picNum = substr($filename, strpos($filename, 'tmp')+3, 1);
				$result = array(
					'status' => 'OK',
					'picNum' => $picNum
				);
				
				unlink($fullPath);
			} else {
				$result = array(
					'status' => 'ERROR'
				);
			}
		} else {
			$selectRtbIdFromPictureId = $db->prepare("SELECT reisetagebuecher.id FROM reisetagebuecher JOIN eintraege ON (eintraege.reisetagebuch_id = reisetagebuecher.id) JOIN eintraege_bilder ON (eintraege.id = eintraege_bilder.eintrag_id) WHERE eintraege_bilder.bild_id = ? AND reisetagebuecher.users_id = ?");
			$selectRtbIdFromPictureId->execute(array($pictureId, $userId));
			$rtbIdFromPictureId = $selectRtbIdFromPictureId->fetchAll(\PDO::FETCH_ASSOC);
			if(!empty($rtbIdFromPictureId)){
				$rtbId = $rtbIdFromPictureId[0]['id'];
				if(isOwner($db, $userId, $rtbId)){
					// Löscht die Datei
					unlink($fullPath);
					// Löschen aus der Datenbank
					$deletePictureFromEintraege = $db->prepare("DELETE FROM eintraege_bilder WHERE bild_id = ?");
					$deletePictureFromEintraege->execute(array($pictureId));
					$deletePictureFromBilder = $db->prepare("DELETE FROM bilder WHERE id = ?");
					$deletePictureFromBilder->execute(array($pictureId));
					$result = array(
						'status' => 'OK',
						'picNum' => 0
					);
				} else {
					$result = array(
						'status' => 'ERROR'
					);
				}
			}
		}
		echo json_encode($result);
	}
?>