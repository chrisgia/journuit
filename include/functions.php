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
		$rtbUrl = htmlspecialchars($rtbUrl);
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

	// PHPMailer importieren
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	function sendMail($to, $name, $subject, $message, $copy = false, $attachments = NULL) {
      	$mail = new PHPMailer(true);
      	$body = $message;
      	$mail->IsSMTP();
      	$mail->Host = 'sslout.df.eu;smtprelaypool.ispgateway.de';
		$mail->SMTPAuth = true;                              
		$mail->Username = 'kontakt@euresa-reisen.de';                 
		$mail->Password = 'q4P94DjkY7/t!908_';                           
		$mail->SMTPSecure = 'ssl';                            
		$mail->Port = 465; 
		$mail->CharSet = 'utf-8';
		$mail->setLanguage('de', '/phpmailer/language/');
		$mail->isHTML(true);
       	$mail->SetFrom('info@euresa-reisen.de', 'journuit');
       	$mail->AddReplyTo("info@euresa-reisen.de","journuit");
      	$mail->Subject    = $subject;
      	$mail->AltBody    = trim(strip_tags($body));
      	$mail->Body = $body;
      	$address = $to;
      	$mail->AddAddress($address, $name);

        //Copy (CC) hinzufügen
        if($copy == true){
            $mail->AddBCC('info@euresa-reisen.de');
        }

      	// Anhang / Anhänge hinzufügen
      	if($attachments !== NULL){
      		if(is_array($attachments)){           
	            foreach($attachments as $attachment) {
	              $mail->AddAttachment($attachment[0], $attachment[1]);
	            }
      		} elseif (is_string($attachments)) {
      			$mail->AddAttachment($attachments);
      		}
      	}

      	// eigentlicher Mailversand
      	if(!$mail->Send()) {
        	return $mail->ErrorInfo;
      	} else {
            return 1;
     	}
	}

	function removePicture($db, $picture, $username, $userId){
		$file = htmlspecialchars($picture);
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
			//Checken ob es sich um ein Titelbild handelt
			$isRtb = false;
			$selectRtbId = $db->prepare("SELECT id FROM reisetagebuecher WHERE bild_id = ? AND users_id = ?");
			$selectRtbId->execute(array($pictureId, $userId));
			$rtbId = $selectRtbId->fetchAll(\PDO::FETCH_ASSOC);
			if(empty($rtbId)){
				$selectRtbIdFromPictureId = $db->prepare("SELECT reisetagebuecher.id FROM reisetagebuecher JOIN eintraege ON (eintraege.reisetagebuch_id = reisetagebuecher.id) JOIN eintraege_bilder ON (eintraege.id = eintraege_bilder.eintrag_id) WHERE eintraege_bilder.bild_id = ? AND reisetagebuecher.users_id = ?");
				$selectRtbIdFromPictureId->execute(array($pictureId, $userId));
				$rtbId = $selectRtbIdFromPictureId->fetchAll(\PDO::FETCH_ASSOC);
			} else {
				$isRtb = true;
			}
			if(!empty($rtbId)){
				$rtbId = $rtbId[0]['id'];
				if(isOwner($db, $userId, $rtbId)){
					// Löscht die Datei
					unlink($fullPath);
					// Löschen aus der Datenbank
					if(!$isRtb){
						$deletePictureFromEintraege = $db->prepare("DELETE FROM eintraege_bilder WHERE bild_id = ?");
						$deletePictureFromEintraege->execute(array($pictureId));
					}
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
		return $result;
	}

	function deleteEintrag($db, $rtbUrl, $eintragId, $username, $userId){
		$rtbId = getRtbIdFromUrl($db, htmlspecialchars($rtbUrl));
		$eintragId = htmlspecialchars($eintragId);
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
		return $result;
	}
?>