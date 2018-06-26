<?php
	// Leitet den Benutzer weiter, wenn er versucht eine Seite für angemeldete Benutzer aufzurufen
	function checkAuthorization($userId, $view, $pages){
		if(empty($userId) && in_array($view, $pages)){
			header('location: /pages/unpermitted.php');
		}
	}

	// Erstellt eine zufällige ID die noch nicht in der Tabelle vorhanden ist
	function uniqueDbId($db, $table, $field){
		$randomId = $db->prepare("
			SELECT random_num
			FROM (
			  SELECT FLOOR(RAND() * 99999999999999) AS random_num 
			  UNION
			  SELECT FLOOR(RAND() * 99999999999999) AS random_num
			) AS numbers_mst_plus_1
			WHERE `random_num` NOT IN (SELECT ? FROM ".htmlspecialchars($table).")
			LIMIT 1"
		);
		$randomId->execute(array($field));
		$id = $randomId->fetchAll(\PDO::FETCH_ASSOC);
		$id = $id[0]['random_num'];
		return $id;
	}


	// Funktion um die Lat und Lon Werte aus den EXIF Daten zu bekommen
	function gps($coordinate, $hemisphere) {
	  if (is_string($coordinate)) {
		$coordinate = array_map("trim", explode(",", $coordinate));
	  }
	  for ($i = 0; $i < 3; $i++) {
		$part = explode('/', $coordinate[$i]);
		if (count($part) == 1) {
		  $coordinate[$i] = $part[0];
		} else if (count($part) == 2) {
		  $coordinate[$i] = floatval($part[0])/floatval($part[1]);
		} else {
		  $coordinate[$i] = 0;
		}
	  }
	  list($degrees, $minutes, $seconds) = $coordinate;
	  $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
	  return $sign * ($degrees + $minutes/60 + $seconds/3600);
	}

	function getExifData($filePath){
		$exif = exif_read_data($filePath);
			
		$dateTime = NULL;
		 // Speichern des Datums andem das Bild genommen wurde, falls es vorhanden ist
		if(isset($exif["DateTimeOriginal"])){
			$dateTime = $exif['DateTimeOriginal'];
		}

		$lat = NULL;
		$lon = NULL;
		// Speichern der Latitude und Longitude Werte, falls diese vorhanden sind
		if(isset($exif["GPSLatitude"], $exif["GPSLongitude"])){
			$lat = gps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
			$lon = gps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);
		}

		$result = array(
			'dateTime' => $dateTime, 
			'lat' => $lat,
			'lon' => $lon
		);

		return $result;
	}

	function insertBild($db, $username, $id, $file_ext, $picNumber = null) {
		$exifSupportedFileExts = array('jpg', 'jpeg', 'jpe', 'jif', 'jfif', 'jfi');
		if(is_null($picNumber)){
			$picNumber = '';
		}
		$tempPath = "../users/$username/tmp".$picNumber."_".$id.".".$file_ext;
		// Falls der Benutzer im Formular eine eigene pictureId übergibt
		if(file_exists($tempPath)){
			$fullPath = "../users/$username/".$id.".".$file_ext;
			rename($tempPath, $fullPath);
			if(in_array(strtolower($file_ext), $exifSupportedFileExts)){
				$exifData = getExifData($fullPath);
				
				$insertPictureData = $db->prepare("INSERT INTO bilder(id, datum, lat, lon, file_ext) VALUES(?, ?, ?, ?, ?)");
				$result = $insertPictureData->execute(array(htmlspecialchars($id), htmlspecialchars($exifData['dateTime']), htmlspecialchars($exifData['lat']), htmlspecialchars($exifData['lon']), htmlspecialchars($file_ext)));
			} else {
				$insertPictureData = $db->prepare("INSERT INTO bilder(id, file_ext) VALUES(?, ?)");
				$result = $insertPictureData->execute(array(htmlspecialchars($id), htmlspecialchars($file_ext)));
			}
			return $result;
		} else {
			return false;
		}
	}

	function updateBild($db, $username, $previousId, $id, $previous_file_ext, $file_ext){
		$exifSupportedFileExts = array('jpg', 'jpeg', 'jpe', 'jif', 'jfif', 'jfi');
		$tempPath = "../users/$username/tmp_".$id.".".$file_ext;
		$fullPath = "../users/$username/".$previousId.".".$previous_file_ext;
		unlink($fullPath);

		if(file_exists($tempPath)){
			$fullPath = "../users/$username/".$id.".".$file_ext;
			rename($tempPath, $fullPath);
			if(in_array(strtolower($file_ext), $exifSupportedFileExts)){
				$exifData = getExifData($fullPath);
				$updatePictureData = $db->prepare("UPDATE bilder SET id = ?, datum = ?, lat = ?, lon = ?, file_ext = ? WHERE id = ?");
				$result = $updatePictureData->execute(array($id, htmlspecialchars($exifData['dateTime']), htmlspecialchars($exifData['lat']), htmlspecialchars($exifData['lon']), $file_ext, $previousId));
			} else {
				$updatePictureData = $db->prepare("UPDATE bilder SET id = ?, file_ext = ? WHERE id = ?");
				$result = $updatePictureData->execute(array($id, $file_ext, $previousId));
			}
			return $result;
		} else {
			return false;
		}
	}

	function insertEintragBild($db, $username, $eintrag_id, $bild_id, $bildunterschrift = null) {
		$insertEintragBild = $db->prepare("INSERT INTO eintraege_bilder(eintrag_id, bild_id, bildunterschrift) VALUES(?, ?, ?)");
		$result = $insertEintragBild->execute(array(htmlspecialchars($eintrag_id), htmlspecialchars($bild_id), htmlspecialchars($bildunterschrift)));
		return $result;
	}

	// Datum aus Formulareingabe für MySQL Insert aufbereiten
	function getMySqlDate($date) {
		$date = date("d.m.Y", strtotime($date));
		return $date;
	}

	// Funktion um sicherzustellen dass der Benutzer auf sein eigenes Reisetagebuch zugreifft
	function isOwner($db, $userId, $rtbId){
		$selectRtbFromId = $db->prepare("SELECT id FROM reisetagebuecher WHERE id = ? AND users_id = ?");
		$selectRtbFromId->execute(array($rtbId, $userId));
		$rtbFromId = $selectRtbFromId->fetchAll(\PDO::FETCH_ASSOC);
		if(!empty($rtbFromId)){
			return $rtbFromId[0]['id'] == $rtbId;
		}

		return false;  
	}

	function getRtbIdFromUrl($db, $rtbUrl){
		$selectRtbIdFromURL = $db->prepare("SELECT id FROM reisetagebuecher WHERE url = ?");
		$selectRtbIdFromURL->execute(array($rtbUrl));
		$rtbId = $selectRtbIdFromURL->fetchAll(\PDO::FETCH_ASSOC);
		if(!empty($rtbId)){
			return $rtbId[0]['id'];
		} 

		return false;
	}

	function checkEntryTime($db, $rtbId, $datum, $uhrzeit){
		$selectUhrzeiten = $db->prepare("SELECT id FROM eintraege WHERE reisetagebuch_id = ? AND datum = ? AND uhrzeit = ?");
		$selectUhrzeiten->execute(array($rtbId, $datum, $uhrzeit));
		$uhrzeiten = $selectUhrzeiten->fetchAll(\PDO::FETCH_ASSOC);

		if(empty($uhrzeiten)){
			return true;
		} 

		return false;
	}

	function cleanFolder($username){
		$mask = "../users/$username/tmp*.*";
		array_map('unlink', glob($mask));
	}
?>