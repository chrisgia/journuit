<?php
require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

if(isset($_FILES['files'])){
	// Man nimmt den Benutzernamen des eingeloggten Benutzers um später den Ordner zu erstellen
	$id = $auth->getUserId();
	$selectUsername = $db->prepare("SELECT username FROM users WHERE id = ?");
	$selectUsername->execute(array($id));
	$username = $selectUsername->fetchAll(\PDO::FETCH_ASSOC);
	$username = $username[0]['username'];

	// Dateiinformationen
    $file_name = $_FILES['files']['name'][0];
    $file_size = $_FILES['files']['size'][0];
    $file_tmp = $_FILES['files']['tmp_name'][0];
    $file_type = $_FILES['files']['type'][0];
    $file_ext = substr($file_name, strpos($file_name, ".") + 1);
    $pictureId = uniqueDbId($db, 'bilder', 'id');
    $filename = $pictureId.".".$file_ext;
    $fullPath = "../users/$username/".$filename;

    // Sicherstellen, dass es die Datei noch nicht gibt
    while(file_exists($fullPath)){
		$pictureId = uniqueDbId($db, 'bilder', 'id');
		$filename = $pictureId.".".$file_ext;
    	$fullPath = "../users/$username/".$filename;
    }

    // Das Bild wird mittels TinyPNG API kompressiert
    $source = \Tinify\fromFile($file_tmp);
    // Man behält das Erstellungsdatum und die GPS-Daten
	$sourcePreservedEXIF = $source->preserve("creation", "location");
	// Das Bild wird für Titelbilder angepasst
	$resizedPicture = $sourcePreservedEXIF->resize(array(
	    "method" => "cover",
	    "width" => 350,
	    "height" => 350
	));
	// Die Datei wird in das Benutzerverzeichnis verschoben
	$resizedPicture->toFile($fullPath);

	$infos = array(
		'pictureId' => $pictureId, 
		'file_ext' => $file_ext
	);
	
	echo json_encode($infos);
}
?>