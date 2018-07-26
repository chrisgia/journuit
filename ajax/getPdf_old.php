<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	// FPDF-Klasse einbinden
	require_once('../vendor/fpdf/fpdf.php');

	if(isset($_POST['rtb'])){

		$rtbUrl = htmlspecialchars($_POST['rtb']);
		$rtbId = getRtbIdFromUrl($db, $rtbUrl);

		$mask = '../files/'.$rtbUrl.'/*.pdf';
		array_map('unlink', glob($mask));

		// Reisetagebuch auswählen
		$selectReisetagebuch = $db->prepare("SELECT reisetagebuecher.id, users.username, reisetagebuecher.users_id, titel, url, beschreibung, public, erstellt_am, bild_id, bilder.file_ext FROM reisetagebuecher LEFT JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) JOIN users ON (users_id = users.id) WHERE reisetagebuecher.id = ?");
		$selectReisetagebuch->execute(array($rtbId));
		$reisetagebuch = $selectReisetagebuch->fetchAll(\PDO::FETCH_ASSOC);

		if($userId == $reisetagebuch[0]['users_id'] || ($userId != $reisetagebuch[0]['users_id'] && $reisetagebuch[0]['public'] == 1)){

			$rtbCreator = $reisetagebuch[0]['username'];
			$rtbTitel = iconv("UTF-8", "Windows-1252//TRANSLIT", $reisetagebuch[0]['titel']);
			$rtbBeschreibung = iconv("UTF-8", "Windows-1252//TRANSLIT", $reisetagebuch[0]['beschreibung']);

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
					$this->Image('../pictures/journuit-logo_big.png', $this->GetPageWidth() - 20, 3, -300, -300, 'png', 'https://journuit.euresa-reisen.de');
				}

			    function Footer() {
			        // Go to 1.5 cm from bottom
				    $this->SetY(-15);
				    // Select Arial italic 9
				    $this->SetFont('Arial', 'I', 9);
				    // Print centered page number
				    $this->Cell(0, 10, "Seite ".$this->PageNo()." von {nb}", 0, 0, 'C');
			    }
			}

			$reisetagebuchPdf = new PDF("P", "mm", "A4"); // L=Querformat(Landscape), P=Hochformat(Portrait)
			$reisetagebuchPdf->SetAuthor($fullname);
			$reisetagebuchPdf->SetCreator('journuit - FPDF');
			$reisetagebuchPdf->SetTitle($rtbTitel);
			$reisetagebuchPdf->SetSubject($rtbBeschreibung);
			$reisetagebuchPdf->AliasNbPages();

			// Linien Weiß
			$reisetagebuchPdf->SetDrawColor(255);
			// Seite erzeugen (sozusagen: starten)
			$reisetagebuchPdf->AddPage();

			//Überschrift
			$reisetagebuchPdf->SetFont('Helvetica');
			$reisetagebuchPdf->SetFontSize(26);
			$reisetagebuchPdf->Text(($reisetagebuchPdf->GetPageWidth() - $reisetagebuchPdf->GetStringWidth($rtbTitel)) / 2, 15, $rtbTitel);
			$reisetagebuchPdf->SetFontSize(14);
			$reisetagebuchPdf->Text(($reisetagebuchPdf->GetPageWidth() / 2) + $reisetagebuchPdf->GetStringWidth($rtbTitel), 15, ', von '.$rtbCreator);
			/*$reisetagebuchPdf->Cell($reisetagebuchPdf->GetPageWidth(), 10, $rtbTitel, 0, 2, 'C');
			$reisetagebuchPdf->SetFontSize(14);
			$reisetagebuchPdf->Cell($reisetagebuchPdf->GetPageWidth(), 10, 'von '.$rtbCreator, 0, 2, 'C');*/

			if(!empty($reisetagebuch[0]['bild_id'])){
				$reisetagebuchPdf->Image('../users/'.$reisetagebuch[0]['username'].'/'.$reisetagebuch[0]['bild_id'].'.'.$reisetagebuch[0]['file_ext'], 0, 30, null, null, $reisetagebuch[0]['file_ext'], '../../users/'.$reisetagebuch[0]['username'].'/'.$reisetagebuch[0]['bild_id'].'.'.$reisetagebuch[0]['file_ext']);
			} else {
				$reisetagebuchPdf->Image('../pictures/no-picture.jpg', 25, 30);
			}

			$anzahlEintraege = $anzahlEintraege[0]['anzahl'];
			if($anzahlEintraege == 1){
				$eintraegeText = $anzahlEintraege.' Eintrag';
			} else {
				$eintraegeText = $anzahlEintraege.' '.iconv("UTF-8", "Windows-1252//TRANSLIT", 'Einträge');
			}

			$reisetagebuchPdf->SetY(170);

			$reisetagebuchPdf->SetFontSize(13);
			$reisetagebuchPdf->SetFont('', '');
			$reisetagebuchPdf->MultiCell($reisetagebuchPdf->GetPageWidth() - 30, 10, iconv("UTF-8", "Windows-1252//TRANSLIT", $reisetagebuch[0]['beschreibung']), 0, 1);

			$reisetagebuchPdf->SetFontSize(12);
			$reisetagebuchPdf->SetFont('', 'I');
			$reisetagebuchPdf->Cell(0, 10, $eintraegeText.', erstellt am '.getMySqlDate($reisetagebuch[0]['erstellt_am']).'.', 0, 1);
			$reisetagebuchPdf->Ln(10);
			$reisetagebuchPdf->Image('../files/'.$reisetagebuch[0]['url'].'/linkQrCode.png', ($reisetagebuchPdf->GetPageWidth() - 43) / 2, $reisetagebuchPdf->GetY(), null, null, 'png', 'https://journuit.euresa-reisen.de/pages/reisetagebuecher.php?rtb='.$reisetagebuch[0]['url']);

			if($anzahlEintraege > 0){
				$reisetagebuchPdf->SetFont('', '');
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
							if($eintrag['zusammenfassung'] != 1){
								if($eintrag['uhrzeit'] > 2400){
									$uhrzeit = substr_replace(str_pad($eintrag['uhrzeit'] - 2400, 4, '0', STR_PAD_LEFT), ':', 2, 0);
									$reisetagebuchPdf->Cell($reisetagebuchPdf->GetStringWidth('24:00 +'.$uhrzeit) + 3, 10, '24:00 +'.$uhrzeit, 0, 0);
								} else {
									$uhrzeit = substr_replace($eintrag['uhrzeit'], ':', 2, 0);
									$reisetagebuchPdf->Cell(15, 10, $uhrzeit, 0, 0);
								}
							}
							
						}
						$reisetagebuchPdf->SetFont('', 'B');
						$reisetagebuchPdf->Cell(0, 10, iconv("UTF-8", "Windows-1252//TRANSLIT", $eintrag['titel']), 0, 0);
						if($eintrag['zusammenfassung'] != 1){
							$reisetagebuchPdf->SetFont('', 'I');
							$reisetagebuchPdf->SetX(($reisetagebuchPdf->GetPageWidth() - $reisetagebuchPdf->GetStringWidth($standortName)) - 10);
							$reisetagebuchPdf->Cell(0, 10, iconv("UTF-8", "Windows-1252//TRANSLIT", $standortName), 0, 1);
						} else {
							$reisetagebuchPdf->Ln(10);
						}
						$reisetagebuchPdf->SetFont('', '');
						$reisetagebuchPdf->MultiCell($reisetagebuchPdf->GetPageWidth() - 30, 5, iconv("UTF-8", "Windows-1252//TRANSLIT", $eintrag['text']), 0, 1);
						$reisetagebuchPdf->Ln(5);
						foreach($bilder as $bild){
							$reisetagebuchPdf->Image('../users/'.$reisetagebuch[0]['username'].'/'.$bild['id'].'.'.$bild['file_ext'], null, null, -150, -150, $bild['file_ext'], '../../users/'.$reisetagebuch[0]['username'].'/'.$bild['id'].'.'.$bild['file_ext']);
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
			}

			$reisetagebuchPdf->Output('../files/'.$rtbUrl.'/'.$rtbCreator.'_'.normalize($reisetagebuch[0]['titel']).'.pdf', 'F');
		}
	} else {
		echo 'Direkter Aufruf geblockt !';
	}
?>