<?php
/*require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php';
$randomPictureId = $db->prepare("
	SELECT FLOOR(RAND() * 99999) AS random_num
	FROM eintraege_bilder 
	WHERE `random_num` NOT IN (SELECT bild_id FROM eintraege_bilder)
	LIMIT 1"
);
$randomPictureId->execute();
$pictureId = $randomPictureId->fetchAll(\PDO::FETCH_ASSOC);
*/
if(isset($_FILES['files'])){
    $file_name = $_FILES['files']['name'][0];
    $file_size = $_FILES['files']['size'][0];
    $file_tmp = $_FILES['files']['tmp_name'][0];
    $file_type = $_FILES['files']['type'][0];

    move_uploaded_file($file_tmp, "uploads/titelbilder/".strtolower($file_name));
}
?>