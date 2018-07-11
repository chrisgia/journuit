<?php 
	header("Access-Control-Allow-Origin: *");
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT']."/vendor/phpqrcode/qrlib.php";

	if(isset($_POST['url'])){
		$url = 'http://www.landausflugsplaner.de/pages/reisetagebuecher.php?rtb='.htmlspecialchars($_POST['url']);
		$qrCodePicPath = "../files/".htmlspecialchars($_POST['url'])."/linkQrCode.png";

		QRCode::png($url, $qrCodePicPath,'M', 4, 2);
		echo "<img class=\"uk-align-center\" src=\"".$qrCodePicPath."\">"; 
	}
?>