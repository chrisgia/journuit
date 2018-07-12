<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php';
	// reference the Dompdf namespace
	use Dompdf\Dompdf;
	// instantiate and use the dompdf class
	$dompdf = new Dompdf();

	$testHtml = '<span style="color:red;">test2</span>';
	$dompdf->loadHtml($testHtml);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper('A4', 'portrait');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream();
?>