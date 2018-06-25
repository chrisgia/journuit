<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_POST['picture'])){
		$file = '..'.htmlspecialchars($_POST['picture']);
		$filename = basename($file);
		$dotPos = strrpos($filename, ".");
		$pictureId = substr($filename, 0, $dotPos); 

		$selectRtbIdFromPictureId = $db->prepare("SELECT reisetagebuecher.id FROM reisetagebuecher JOIN eintraege ON (eintraege.reisetagebuch_id = reisetagebuecher.id) JOIN eintraege_bilder ON (eintraege.id = eintraege_bilder.eintrag_id) WHERE eintraege_bilder.bild_id = ? AND reisetagebuecher.users_id = ?");
        $selectRtbIdFromPictureId->execute(array($pictureId, $userId));
        $rtbIdFromPictureId = $selectRtbIdFromPictureId->fetchAll(\PDO::FETCH_ASSOC);
        if(!empty($rtbIdFromPictureId)){
        	$rtbId = $rtbIdFromPictureId[0]['id'];
        	if(isOwner($db, $userId, $rtbId)){
        		// Löscht die Datei
        		unlink($file);
        		// Löschen aus der Datenbank
        		$deletePictureFromEintraege = $db->prepare("DELETE FROM eintraege_bilder WHERE bild_id = ?");
       			$deletePictureFromEintraege->execute(array($pictureId));
       			$deletePictureFromBilder = $db->prepare("DELETE FROM bilder WHERE id = ?");
       			$deletePictureFromBilder->execute(array($pictureId));
        		$result = array(
        			'status' => 'OK'
        		);
        	} else {
        		$result = array(
        			'status' => 'ERROR'
        		);
        	}
        }
        echo json_encode($result);
	}
?>