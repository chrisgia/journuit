<?php
	// reference the Dompdf namespace
	use Dompdf\Dompdf;
	// instantiate and use the dompdf class
	$dompdf = new Dompdf();

	$testHtml = '<span class="uk-text-bold uk-h3 uk-margin-right">test2</span>';
	$dompdf->loadHtml($testHtml);

	// (Optional) Setup the paper size and orientation
	$dompdf->setPaper('A4', 'portrait');

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream();
?>