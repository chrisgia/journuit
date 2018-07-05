<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_POST['standortId'])){
		$standortId = htmlspecialchars($_POST['standortId']);

		$selectStandortbild = $db->prepare("SELECT bild_id, bilder.file_ext FROM standorte JOIN bilder ON (standorte.bild_id = bilder.id) WHERE standorte.id = ? AND standorte.users_id = ?");
		$selectStandortbild->execute(array($standortId, $userId));
		$standortbild = $selectStandortbild->fetchAll(\PDO::FETCH_ASSOC);

		if(!empty($standortbild)){
			$picture = "/".$standortbild[0]['bild_id'].".".$standortbild[0]['file_ext'];
			removePicture($db, $picture, $username, $userId);
		}

		$deleteStandort = $db->prepare("DELETE FROM standorte WHERE id = ? AND users_id = ?");
		$deleteStandort->execute(array($standortId, $userId));

		if($deleteStandort){
			$result = array(
				'status' => 'OK'
			);
		} else {
			$result = array(
				'status' => 'ERROR'
			);
		}

		echo json_encode($result);
	}
?>