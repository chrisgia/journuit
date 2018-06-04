<?php
	// Erstellt eine zufällige ID die noch nicht in der Tabelle vorhanden ist
	function uniqueDbId($db, $table, $field){
		$randomId = $db->prepare("
			SELECT random_num
			FROM (
			  SELECT FLOOR(RAND() * 99999) AS random_num 
			  UNION
			  SELECT FLOOR(RAND() * 99999) AS random_num
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

	function insertBild($db, $username, $id, $file_ext) {
		$exifSupportedFileExts = array('jpg', 'jpeg', 'jpe', 'jif', 'jfif', 'jfi');
		$tempPath = "../users/$username/tmp_".$id.".".$file_ext;
		$fullPath = "../users/$username/".$id.".".$file_ext;
		rename($tempPath, $fullPath);
	    if(in_array(strtolower($file_ext), $exifSupportedFileExts)){
		    $exif = exif_read_data($fullPath);
		    
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
		    $insertPictureData = $db->prepare("INSERT INTO bilder(id, datum, lat, lon, file_ext) VALUES(?, ?, ?, ?, ?)");
			$result = $insertPictureData->execute(array(htmlspecialchars($id), htmlspecialchars($dateTime), htmlspecialchars($lat), htmlspecialchars($lon), htmlspecialchars($file_ext)));
		} else {
			$insertPictureData = $db->prepare("INSERT INTO bilder(id, file_ext) VALUES(?, ?)");
			$result = $insertPictureData->execute(array(htmlspecialchars($id), htmlspecialchars($file_ext)));
		}
		return $result;
	}
?>