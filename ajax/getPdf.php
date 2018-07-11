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
				//Grauer Hintergrund und journuit Logo auf jeder Seite
				function Header() {
			    	$this->Image('../pictures/pdf-background.png', 0, 0, $this->GetPageWidth(), $this->GetPageHeight());
					$this->Image('../pictures/journuit-logo_big.png', $this->GetPageWidth() - 20, 3, -300);
				}

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

			$reisetagebuchPdf->SetDrawColor(255);
			// Seite erzeugen (sozusagen: starten)
			$reisetagebuchPdf->AddPage();

			//Überschrift
			$reisetagebuchPdf->SetFont('Helvetica');
			$reisetagebuchPdf->SetFontSize(26);
			$reisetagebuchPdf->Text(($reisetagebuchPdf->GetPageWidth() / 2) - $reisetagebuchPdf->GetStringWidth($rtbTitel), 15, $rtbTitel);
			$reisetagebuchPdf->SetFontSize(14);
			$reisetagebuchPdf->Text(($reisetagebuchPdf->GetPageWidth() / 2), 15, ', von '.$rtbCreator);

			if(!empty($reisetagebuch[0]['bild_id'])){
				$reisetagebuchPdf->Image('../users/'.$reisetagebuch[0]['username'].'/'.$reisetagebuch[0]['bild_id'].'.'.$reisetagebuch[0]['file_ext'], 0, 30, null, null, $reisetagebuch[0]['file_ext'], '../'.$reisetagebuch[0]['username'].'/'.$reisetagebuch[0]['bild_id'].'.'.$reisetagebuch[0]['file_ext']);
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
				$reisetagebuchPdf->Ln(5);

				// Einträge des Datums auwählen
				$selectEintraege = $db->prepare("SELECT id, titel, text, uhrzeit, standort_id, zusammenfassung, public FROM eintraege WHERE reisetagebuch_id = ? AND entwurf = 0 AND datum = ? ORDER BY uhrzeit ASC");
				$selectEintraege->execute(array($rtbId, $datum['datum']));
				$eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);

				$reisetagebuchPdf->SetFontSize(13);
				$count = 1;
				foreach($eintraege as $eintrag){
					// Eintragsort auswählen
					$selectStandort = $db->prepare("SELECT name FROM standorte WHERE id = ?");
					$selectStandort->execute(array($eintrag['standort_id']));
					$standort = $selectStandort->fetchAll(\PDO::FETCH_ASSOC);
					if(!empty($standort)){
						$standortName = $standort[0]['name'];
					} else {
						$standortName = '?';
					}

					// Eintragsbilder auswählen			
					$selectBilder = $db->prepare("SELECT bilder.id, bilder.file_ext FROM bilder JOIN eintraege_bilder ON (bilder.id = eintraege_bilder.bild_id) WHERE eintraege_bilder.eintrag_id = ?");
					$selectBilder->execute(array($eintrag['id']));
					$bilder = $selectBilder->fetchAll(\PDO::FETCH_ASSOC);
					if($eintrag['zusammenfassung'] != 1){
						$uhrzeit = substr_replace($eintrag['uhrzeit'], ':', 2, 0);
						$reisetagebuchPdf->Cell(15, 10, $uhrzeit, 0, 0);
					}
					$reisetagebuchPdf->SetFont('', 'B');
					$reisetagebuchPdf->Cell(0, 10, $eintrag['titel'], 0, 0);
					if($eintrag['zusammenfassung'] != 1){
						$reisetagebuchPdf->SetFont('', 'I');
						$reisetagebuchPdf->SetX(($reisetagebuchPdf->GetPageWidth() - $reisetagebuchPdf->GetStringWidth($standortName)) - 10);
						$reisetagebuchPdf->Cell(0, 10, $standortName, 0, 1);
					} else {
						$reisetagebuchPdf->Ln(10);
					}
					$reisetagebuchPdf->SetFont('', '');
					$reisetagebuchPdf->MultiCell($reisetagebuchPdf->GetPageWidth() - 30, 5, $eintrag['text'], 0, 1);
					$reisetagebuchPdf->Ln(5);
					foreach($bilder as $bild){
						$reisetagebuchPdf->Image('../users/'.$reisetagebuch[0]['username'].'/'.$bild['id'].'.'.$bild['file_ext'], null, null, -150, -150, $bild['file_ext'], '../'.$reisetagebuch[0]['username'].'/'.$bild['id'].'.'.$bild['file_ext']);
						$reisetagebuchPdf->Ln(5);
					}

					// Linie streichen wenn es nicht der letzte Eintrag dieses Datums ist
					if($count != sizeof($eintraege)){
						$reisetagebuchPdf->Line($reisetagebuchPdf->GetX(), $reisetagebuchPdf->GetY(), 50, $reisetagebuchPdf->GetY());
						$reisetagebuchPdf->Ln(5);
					}

					$count++;
				}
			}

			$reisetagebuchPdf->Output('../users/'.$username.'/'.$rtbCreator.'_'.$rtbTitel.'.pdf', 'F');
		}
	}
?>