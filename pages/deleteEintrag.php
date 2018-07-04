<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_POST['eintragId'], $_POST['rtb'])){
		$rtbId = getRtbIdFromUrl($db, htmlspecialchars($_POST['rtb']));
		$eintragId = htmlspecialchars($_POST['eintragId']);
		if(isOwner($db, $userId, $rtbId)){
			$selectBilder = $db->prepare("SELECT bilder.id, bilder.file_ext FROM eintraege_bilder LEFT JOIN bilder ON (bilder.id = eintraege_bilder.bild_id) WHERE eintrag_id = ?");
			$selectBilder->execute(array($eintragId));
			$bilder = $selectBilder->fetchAll(\PDO::FETCH_ASSOC);
			if(!empty($bilder)){
				foreach($bilder as $bild){
					$picture = "/".$bild['id'].".".$bild['file_ext'];
					removePicture($db, $picture, $username, $userId);
				}
			}
			$deleteEintrag = $db->prepare("DELETE FROM eintraege WHERE id = ? AND reisetagebuch_id = ?");
			$deleteEintrag->execute(array($eintragId, $rtbId));
			if($deleteEintrag){
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
	}
?>