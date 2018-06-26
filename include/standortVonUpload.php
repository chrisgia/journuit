<?php
require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

if(isset($_FILES['files'])){
	// TODO : Filesize und Type checken
	// Man nimmt den Benutzernamen des eingeloggten Benutzers um später den Ordner zu erstellen
	$selectUsername = $db->prepare("SELECT username FROM users WHERE id = ?");
	$selectUsername->execute(array($userId));
	$username = $selectUsername->fetchAll(\PDO::FETCH_ASSOC);
	$username = $username[0]['username'];

	// Dateiinformationen
	$file_name = $_FILES['files']['name'][0];
	$file_size = $_FILES['files']['size'][0];
	$file_tmp = $_FILES['files']['tmp_name'][0];
	$file_ext = substr($file_name, strpos($file_name, ".") + 1);

	$exifSupportedFileExts = array('jpg', 'jpeg', 'jpe', 'jif', 'jfif', 'jfi');
	
	if(in_array(strtolower($file_ext), $exifSupportedFileExts)){
		$exifData = getExifData($file_tmp);
		$latLon = array(
			'lat' => $exifData['lat'], 
			'lon' => $exifData['lon']
		);
		
	} else {
		$error = array(
			'error' => "Dieser Dateityp enthält keine EXIF Daten."
		);
	}

	if(!isset($error)){
		if(empty($exifData['lat']) || empty($exifData['lon'])){
			$error = array(
				'error' => "Der Standort konnte nicht ermittelt werden."
			);
		}
	}
	
	if(!isset($error)){
		echo json_encode($latLon);
	} else {
		echo json_encode($error);
	}
}
?>