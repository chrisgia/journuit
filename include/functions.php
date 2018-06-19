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


	// Funktion um später die Lat und Lon Werte aus den EXIF Daten zu bekommen
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

	function insertBild($db, $username, $id, $file_ext) {
		$exifSupportedFileExts = array('jpg', 'jpeg', 'jpe', 'jif', 'jfif', 'jfi');
		$tempPath = "../users/$username/tmp_".$id.".".$file_ext;
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
?>