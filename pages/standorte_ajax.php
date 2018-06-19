<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	// Füllen der Standort-Selectbox
	if(isset($_POST['standortname'], $_POST['lat'], $_POST['lon'])){
		$errors = array();
		if (ctype_space(htmlspecialchars($_POST['standortname'])) || empty($_POST['standortname'])) {
			array_push($errors, 'Der Name darf nicht leer sein.');
		}

		if (ctype_space(htmlspecialchars($_POST['lat'])) || empty($_POST['lat']) || ctype_space(htmlspecialchars($_POST['lon'])) || empty($_POST['lon'])) {
			array_push($errors, 'Die Latitude und Longitude Werte dürfen nicht leer sein.');
		}

		if($_POST['pictureId'] != "" && empty($errors)){
			if(!insertBild($db, $username, $_POST['pictureId'], $_POST['file_ext'])) {
				array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
			}
		}

		$preparedStandortName = htmlspecialchars(strtolower($_POST['standortname']));
		// Checken, ob der Benutzer nicht bereits ein Standort mit demselben Namen erstellt hat
        $selectIdentischerStandort = $db->prepare("SELECT id FROM standorte WHERE users_id = ? AND LOWER(name) = ?");
        $selectIdentischerStandort->execute(array($userId, $preparedStandortName));
        $identischerStandort = $selectIdentischerStandort->fetchAll(\PDO::FETCH_ASSOC);

        if (!empty($identischerStandort)) {
			array_push($errors, 'Sie haben bereits einen Standort mit diesem Namen erstellt.');
		}

		if(empty($errors)){
			$insertStandort = $db->prepare("INSERT INTO standorte(users_id, name, beschreibung, lat, lon, bild_id) VALUES(?, ?, ?, ?, ?, ?)");
			$insertStandort->execute(array(htmlspecialchars($userId), htmlspecialchars($_POST['standortname']), htmlspecialchars($_POST['beschreibung']), htmlspecialchars($_POST['lat']), htmlspecialchars($_POST['lon']), htmlspecialchars($_POST['pictureId'])));
			
			$insertedStandortId = $db->lastInsertId();

			$selectStandorte = $db->prepare("SELECT id, name FROM standorte WHERE users_id = ? ORDER BY name");
            $selectStandorte->execute(array($userId));
            $standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);

            $standorteSelectBox = "<option value=\"default\">Standort auswählen</option>";
            $standorteSelectBox .= "<option value=\"neuer-standort\" class=\"uk-text-bold\">Neuer Standort</option>";

            $selected = "";

            foreach($standorte as $standort){
            	if($standort['id'] == $insertedStandortId){
            		$selected = "selected";
            	}
                $standorteSelectBox .= "<option value=\"".$standort['id']."\" ".$selected.">".$standort['name']."</option>";
            }

			$result = array(
				'status' => 'OK',
				'data' => $standorteSelectBox
			);

		} else {
			$errorList = "<ul>";
			foreach($errors as $error){
				$errorList .= "<li>".$error."</li>";
			}
			$errorList .= "</ul>";

			$result = array(
				'status' => 'ERROR',
				'data' => $errorList
			);
		}
		echo json_encode($result);
	}
?>