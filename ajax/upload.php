<?php
require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

if(isset($_FILES['files'])){
	$infos = array();
	// Dateiinformationen
	$file_name = $_FILES['files']['name'][0];
	$file_size = $_FILES['files']['size'][0];
	$file_tmp = $_FILES['files']['tmp_name'][0];
	$file_ext = substr($file_name, strpos($file_name, ".") + 1);
	$pictureId = uniqueDbId($db, 'bilder', 'id');
	$filename = "tmp_".$pictureId.".".$file_ext;

	// Checken ob die Datei ein Bild ist
	if(!exif_imagetype($file_tmp)) {
		$infos = array(
			'status' => 'ERROR',
			'msg' => 'Diese Datei wird nicht unterstützt.'
		);
	}

	if((int) $file_size > 10485760){
		$infos = array(
			'status' => 'ERROR',
			'msg' => 'Die Datei ist zu groß. (maximal 10MB)'
		);
	}

	$mask = "../users/$username/tmp_*.*";

	// Wenn verschiedene Bilder hochgeladen werden, gibt es eine Temporäre Datei pro Bild
	if(isset($_POST['multiple']) && $_POST['multiple'] == true){
		$fieldToFill = 1;
		$tmpFiles = array();
		$anzahlBilder = 0;

		if(isset($_POST['anzahlBilder'])){
			$anzahlBilder = (int)htmlspecialchars($_POST['anzahlBilder']);
		}

		for($i = $anzahlBilder + 1; $i <= 3; $i++){
			$mask = "../users/$username/tmp".$i."_*.*";
			if(!empty(glob($mask))){
				$foundFile = glob($mask)[0];
				array_push($tmpFiles, $foundFile);
			}
		}

		if(!empty($tmpFiles)){
			$fieldToFillPos = strlen("../users/$username/tmp");
			$fieldToFill = (int)substr($tmpFiles[sizeof($tmpFiles) - 1], $fieldToFillPos, 1) + 1;
		} else {
			$fieldToFill = $anzahlBilder + 1;
		}

		if($fieldToFill > 3){
				$infos = array(
					'status' => 'ERROR',
					'msg' => 'Die maximale Anzahl (3) an Bildern wurde schon erreicht. Sie können andere Bilder ersetzen indem Sie diese davor löschen.'
				);
		}

		$filename = "tmp".$fieldToFill."_".$pictureId.".".$file_ext;
	} else {
		$fieldToFill = '';
		// Temporäre Bilder löschen
		array_map('unlink', glob($mask));
	}

	if(empty($infos)){
		$fullPath = "../users/$username/".$filename;

		// Das Bild wird mittels TinyPNG API kompressiert
		$source = \Tinify\fromFile($file_tmp);
		// Man behält das Erstellungsdatum und die GPS-Daten
		$sourcePreservedEXIF = $source->preserve("creation", "location");
		
		// Wenn keine Maßangaben übergeben wurden, komprimierung
		if(isset($_POST['width'], $_POST['height'])){
			$width = (int)$_POST['width'];
			$height = (int)$_POST['height'];
		} else {
			$width = 800;
			$height = 600;
		}

		// Die Datei wird in das Benutzerverzeichnis verschoben
		$resizedPicture = $sourcePreservedEXIF->resize(array(
			"method" => "cover",
			"width" => $width,
			"height" => $height
		));
		
		$resizedPicture->toFile($fullPath);$resizedPicture->toFile($fullPath);

		$infos = array(
			'status' => 'OK',
			'pictureId' => $pictureId, 
			'file_ext' => $file_ext,
			'fieldToFill' => $fieldToFill
		);
	}

	echo json_encode($infos);
}
?>