<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_POST['rtb'])){
		$rtbUrl = htmlspecialchars($_POST['rtb']);
		$rtbId = getRtbIdFromUrl($db, $rtbUrl);
		if(isOwner($db, $userId, $rtbId)){
			$selectEintraege = $db->prepare("SELECT id FROM eintraege WHERE reisetagebuch_id = ?");
			$selectEintraege->execute(array($rtbId));
			$eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);

			if(!empty($eintraege)){
				foreach($eintraege as $eintrag){
					deleteEintrag($db, $_POST['rtb'], $eintrag['id'], $username, $userId);
				}
			}
			$selectTitelbild = $db->prepare("SELECT bild_id, bilder.file_ext FROM reisetagebuecher JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) WHERE reisetagebuecher.id = ?");
			$selectTitelbild->execute(array($rtbId));
			$titelbild = $selectTitelbild->fetchAll(\PDO::FETCH_ASSOC);
			if(!empty($titelbild)){
				$picture = "/".$titelbild[0]['bild_id'].".".$titelbild[0]['file_ext'];
				removePicture($db, $picture, $username, $userId);
			}
			$deleteReisetagebuch = $db->prepare("DELETE FROM reisetagebuecher WHERE id = ? AND users_id = ?");
			$deleteReisetagebuch->execute(array($rtbId, $userId));
			if($deleteReisetagebuch){
				array_map('unlink', glob("../files/$rtbUrl/*"));
				rmdir("../files/$rtbUrl");
				$result = array(
					'status' => 'OK'
				);
			} else {
				$result = array(
					'status' => 'ERROR'
				);
			}
		} else {
			$result = array(
				'status' => 'ERROR'
			);
		}
		echo json_encode($result);
	} else {
		echo 'Direkter Aufruf geblockt !';
	}
?>