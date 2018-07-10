<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	// FPDF-Klasse einbinden
	require_once('../vendor/fpdf/fpdf.php');

	if(isset($_POST['rtb'])){

		// Löschen der vorherigen PDF-Datei
		$mask = "../users/$username/*.pdf";
		array_map('unlink', glob($mask));

		$rtbUrl = htmlspecialchars($_POST['rtb']);
		$rtbId = getRtbIdFromUrl($db, $rtbUrl);

		// Reisetagebuch auswählen
		$selectReisetagebuch = $db->prepare("SELECT reisetagebuecher.id, users.username, reisetagebuecher.users_id, titel, url, beschreibung, public, erstellt_am, bild_id, bilder.file_ext FROM reisetagebuecher LEFT JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) JOIN users ON (users_id = users.id) WHERE reisetagebuecher.id = ?");
		$selectReisetagebuch->execute(array($rtbId));
		$reisetagebuch = $selectReisetagebuch->fetchAll(\PDO::FETCH_ASSOC);

		if($userId == $reisetagebuch[0]['users_id'] || ($userId != $reisetagebuch[0]['users_id'] && $reisetagebuch[0]['public'] == 1)){

			$rtbCreator = $reisetagebuch[0]['username'];
			$rtbTitel = iconv("UTF-8", "Windows-1252//TRANSLIT", $reisetagebuch[0]['titel']);

			// Anzahl an Einträgen
			$selectAnzahlEintraege = $db->prepare("SELECT COUNT(id) AS anzahl FROM eintraege WHERE reisetagebuch_id = ? AND entwurf = 0");
			$selectAnzahlEintraege->execute(array($rtbId));
			$anzahlEintraege = $selectAnzahlEintraege->fetchAll(\PDO::FETCH_ASSOC);

			$selectDates = $db->prepare("SELECT DISTINCT datum FROM eintraege WHERE reisetagebuch_id = ? AND entwurf = 0 ORDER BY datum ASC");
			$selectDates->execute(array($rtbId));
			$dates = $selectDates->fetchAll(\PDO::FETCH_ASSOC);

			class PDF extends FPDF {
			    function Footer() {
			        // Go to 1.5 cm from bottom
				    $this->SetY(-15);
				    // Select Arial italic 8
				    $this->SetFont('Arial','I',9);
				    // Print centered page number
				    $this->Cell(0, 10, "Seite ".$this->PageNo()." von {nb}", 0, 0, 'C');
			    }
			}

			$reisetagebuchPdf = new PDF("P", "mm", "A4"); // L=Querformat(Landscape), P=Hochformat(Portrait)
			$reisetagebuchPdf->AliasNbPages();

			// Einstellungen für das zu erstellende PDF-Dokument
			$reisetagebuchPdf->SetDisplayMode(100);      // wie groß wird Seite angezeigt(in %)
			// Seite erzeugen (sozusagen: starten)
			$reisetagebuchPdf->AddPage();

			// journuit Logo
			$reisetagebuchPdf->Image('../pictures/journuit-logo_big.png', $reisetagebuchPdf->GetPageWidth() - 30, 10, -300);

			//Überschrift
			$reisetagebuchPdf->SetFont('Helvetica');
			$reisetagebuchPdf->SetFontSize(26);
			$reisetagebuchPdf->Text(($reisetagebuchPdf->GetPageWidth() / 2) - $reisetagebuchPdf->GetStringWidth($rtbTitel), 15, $rtbTitel);
			$reisetagebuchPdf->SetFontSize(14);
			$reisetagebuchPdf->Text(($reisetagebuchPdf->GetPageWidth() / 2), 15, ', von '.$rtbCreator);

			if(!empty($reisetagebuch[0]['bild_id'])){
				$reisetagebuchPdf->Image('../users/'.$reisetagebuch[0]['username'].'/'.$reisetagebuch[0]['bild_id'].'.'.$reisetagebuch[0]['file_ext'], 0, 30);
			} else {
				$reisetagebuchPdf->Image('../pictures/no-picture.jpg', 25, 30);
			}

			$reisetagebuchPdf->SetFontSize(12);

			$anzahlEintraege = $anzahlEintraege[0]['anzahl'];
			if($anzahlEintraege == 1){
				$eintraegeText = $anzahlEintraege.' Eintrag';
			} else {
				$eintraegeText = $anzahlEintraege.' '.iconv("UTF-8", "Windows-1252//TRANSLIT", 'Einträge');
			}

			$reisetagebuchPdf->Text(15, 170, $eintraegeText.', erstellt am '.getMySqlDate($reisetagebuch[0]['erstellt_am']).'.');
			$reisetagebuchPdf->AddPage();

			foreach($dates as $datum){

				$reisetagebuchPdf->SetFontSize(14);
				$formatiertesDatum = strftime("%e. %B %Y", strtotime($datum['datum']));
				$reisetagebuchPdf->Cell(0, 10, $formatiertesDatum, 0, 2, 'C');
				$reisetagebuchPdf->Line(0, $reisetagebuchPdf->GetY(), $reisetagebuchPdf->GetPageWidth(), $reisetagebuchPdf->GetY());

				// Einträge des Datums auwählen
				$selectEintraege = $db->prepare("SELECT id, titel, text, uhrzeit, standort_id, zusammenfassung, public FROM eintraege WHERE reisetagebuch_id = ? AND entwurf = 0 AND datum = ? ORDER BY uhrzeit ASC");
				$selectEintraege->execute(array($rtbId, $datum['datum']));
				$eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);

				$reisetagebuchPdf->SetFontSize(12);
				foreach($eintraege as $eintrag){
					$selectBilder = $db->prepare("SELECT bilder.id, bilder.file_ext FROM bilder JOIN eintraege_bilder ON (bilder.id = eintraege_bilder.bild_id) WHERE eintraege_bilder.eintrag_id = ?");
					$selectBilder->execute(array($eintrag['id']));
					$bilder = $selectBilder->fetchAll(\PDO::FETCH_ASSOC);
					if($eintrag['zusammenfassung'] != 1){
						$uhrzeit = substr_replace($eintrag['uhrzeit'], ':', 2, 0);
						$reisetagebuchPdf->Cell(15, 10, $uhrzeit, 0, 0);
					}
					$reisetagebuchPdf->Cell(0, 10, $eintrag['titel'], 0, 2);
				}
			}

			$reisetagebuchPdf->Output('../users/'.$username.'/'.$rtbCreator.'_'.$rtbTitel.'.pdf', 'F');
		}
	}
?>