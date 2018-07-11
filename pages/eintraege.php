<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_GET['datum'])){
		if(!isset($view)){
			$view = "eintrag";
		}
		$eintragsdatum = htmlspecialchars($_GET['datum']);
	}

	if(isset($_GET["view"])) {
		$view = htmlspecialchars($_GET["view"]);
	} elseif(isset($_POST["view"])) {
		$view = htmlspecialchars($_POST["view"]);
	} elseif(!isset($view)) {
		$view = 'not_available';
	}

	if(isset($_POST['rtbId'])){
		$rtbId = htmlspecialchars($_POST['rtbId']);
	} elseif(isset($_GET['rtbId'])){
		$rtbId = htmlspecialchars($_POST['rtbId']);
	}

	if(isset($_POST['rtb'])){
		$rtbUrl = htmlspecialchars($_POST['rtb']);
	} elseif(isset($_GET['rtb'])){
		$rtbUrl = htmlspecialchars($_GET['rtb']);
	}

	if(isset($_GET['id'])){
		$eintragId = htmlspecialchars($_GET['id']);
	}

	$onlyLogged = array('neu', 'bearbeiten');
	checkAuthorization($userId, $view, $onlyLogged);
?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Einträge</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<?php 
			
			// Setzen von Variabeln und check ob der Eintrag existiert
			if(isset($rtbUrl) && !empty($rtbUrl)){
				$rtbId = getRtbIdFromUrl($db, $rtbUrl);
				
				$selectRtbData = $db->prepare("SELECT titel, users.username, public FROM reisetagebuecher JOIN users ON (users_id = users.id) WHERE reisetagebuecher.id = ?");
				$selectRtbData->execute(array($rtbId));
				$rtbData = $selectRtbData->fetchAll(\PDO::FETCH_ASSOC);
				$data = $rtbData;

				if(isset($eintragsdatum)){
					// Die Daten des Eintrags mit dem gegebenen Datum und rtbId ausgeben
					$selectEintraege = $db->prepare("SELECT id, titel, text, uhrzeit, standort_id, zusammenfassung, public FROM eintraege WHERE reisetagebuch_id = ? AND datum = ? AND entwurf = 0 ORDER BY uhrzeit ASC");
					$selectEintraege->execute(array($rtbId, $eintragsdatum));
					$eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);
					$data = $eintraege;
				}

				// Ist der Eintrag nicht vorhanden oder das Reisetagebuch nicht öffentlich und von einem anderen Benutzer, wird man zum default case weitergeleitet (nicht vorhandene Seite)
				if(empty($data) || ($rtbData[0]['public'] != 1 && !isOwner($db, $userId, $rtbId))){
					$view = 'not_available';
				} else {
					$rtbTitel = $rtbData[0]['titel'];
					$rtbCreator = $rtbData[0]['username'];
				}
			}

			switch ($view) {
				case 'neu':
					// Gespeicherte Standorte des Benutzers 
					$selectStandorte = $db->prepare("SELECT id, name FROM standorte WHERE users_id = ? ORDER BY name");
					$selectStandorte->execute(array($userId));
					$standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);
					if(isset($rtbId) && isOwner($db, $userId, $rtbId)){
						?>
						<div class="uk-margin-top uk-margin-bottom">
							<h1 class="uk-text-center">Neuer Eintrag</h1>
							<h2 class="uk-text-center uk-margin-remove-top"><?=$rtbTitel;?></h2>
							<hr class="uk-width-1-1">

							<!-- Modal um neue Standorte zu erstellen, geht auf wenn man "Neuer Standort" in der Selectbox auswählt -->
							<div id="standorteModal" class="uk-flex-top" uk-modal>
								<div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
									<button class="uk-modal-close-default" type="button" uk-close></button>
									<?php require '../include/neuerStandort.php'; ?>
								</div>
							</div>

							<form id="neu" method="POST">
								<fieldset class="uk-fieldset">

									<div class="uk-margin">
										<label>Zusammenfassung <input id="zusammenfassung" name="zusammenfassung" class="uk-checkbox" type="checkbox" value="1"></label>
									</div>

									<div class="uk-margin">
										<!-- Selectbox mit den gespeicherten Standorten des Benutzers -->
										<select id="standorte" class="uk-select uk-form-width-medium" name="standort">
											<option value="default" selected>Standort auswählen</option>
											<option value="neuer-standort" class="uk-text-bold">Neuer Standort</option>
											<?php 
											foreach($standorte as $standort){
												echo "<option value=\"".$standort['id']."\">".$standort['name']."</option>";
											}
											?>
										</select>
									</div>

									<div class="uk-margin">
										<div class="uk-inline" id="dateInput">
											<span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar"></span>
											<?php 
											// Setzen des DateInputs auf die aktuelle Zeit (oder auf das gegebene Datum, falls eins gesetzt ist) und rundet die Minuten zu 5 ab
											$rounded_seconds = round(time() / (5 * 60)) * (5 * 60);
											$currentTime = date("H:i", $rounded_seconds);
											$dateTime = date('Y-m-d', time()).' '.$currentTime;

											if(isset($eintragsdatum)){
												$dateTime = $eintragsdatum.' '.$currentTime;
											}

											?>
											<input type="text" name="dateTime" id="dateTime" value="<?=$dateTime;?>" class="uk-input uk-form-width-medium flatpickr" placeholder="Datum & Uhrzeit" required>
											<div id="uhrzeitError">
												<!-- Hier wird ein Fehler angezeigt, wenn es Bereits einen Eintrag mit der ausgewählten Uhrzeit gibt -->
											</div>
										</div>
									</div>

									<div class="uk-margin">
										<input name="titel" class="uk-input" type="text" placeholder="Titel..." required>
									</div>

									<div class="uk-margin">
										<textarea name="eintrag" class="uk-textarea" rows="5" placeholder="Eintrag..." required></textarea>
									</div>

									<div class="uk-margin">
										<div id="eintragsBildUpload" class="js-upload uk-placeholder uk-text-center">
											<span uk-icon="icon: cloud-upload"></span>
											<span class="uk-text-middle">Bilder hochladen (max. 3, per Drag & Drop oder </span>
											<div uk-form-custom>
												<input type="file" name="files">
												<span class="uk-link">direkter Auswahl</span>)
											</div>
										</div>
										<progress id="js-progressbar3" class="uk-progress" value="0" max="100" hidden></progress>
									</div>

									<div id="loading2" class="uk-text-center" hidden>
										<div uk-spinner></div>
										<span>Das Bild wird verarbeitet...</span>
									</div>

									<div id="picturesError">
										<!-- Hier erscheinen die Fehler beim hochladen von Bildern -->
									</div>

									
									<div id="pictures" class="uk-margin uk-text-center uk-child-width-1-3" uk-grid>
									<!-- Hier erscheinen die hochgeladene Bilder-->
									</div>

									<div class="uk-margin">
										<label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox" value="1"></label>
									</div>

									<input id="picture1Id" name="picture1Id" type="hidden" value="">
									<input id="file1_ext" name="file1_ext" type="hidden" value="">
									<input id="bild1unterschrift" name="bild1unterschrift" type="hidden" value="">
									<input id="picture2Id" name="picture2Id" type="hidden" value="">
									<input id="file2_ext" name="file2_ext" type="hidden" value="">
									<input id="bild2unterschrift" name="bild2unterschrift" type="hidden" value="">
									<input id="picture3Id" name="picture3Id" type="hidden" value="">
									<input id="file3_ext" name="file3_ext" type="hidden" value="">
									<input id="bild3unterschrift" name="bild3unterschrift" type="hidden" value="">

									<input id="rtbUrl" name="rtb" type="hidden" value="<?=$rtbUrl;?>">

								</fieldset>
								<div class="uk-flex uk-flex-center uk-flex-middle">
									<button class="uk-button uk-button-default uk-margin-right" name="entwurf" value="1">Als Entwurf speichern</button>
									<button class="uk-button uk-button-default" name="create">Erstellen</button>
								</div>
							</form>
							<hr class="uk-width-1-1">
						</div>
					<?php
					} else {
						?>
						<div class="uk-margin-top uk-alert-danger" uk-alert>
							<p>Dieses Reisetagebuch ist nicht vorhanden.</p>
						</div>
					<?php
					}

					// Formularverarbeitung 
					if(isset($_POST['standort'], $_POST['dateTime'], $_POST['titel'], $_POST['eintrag'])){
						$errors = array();

						$datum = substr(htmlspecialchars($_POST['dateTime']), 0, 10);
						$uhrzeit = str_replace(':', '', substr(htmlspecialchars($_POST['dateTime']), 11, 5));
						$roundedUhrzeit = round((int) $uhrzeit / 5) * 5;
						$roundedUhrzeit = str_pad($roundedUhrzeit, 4, '0', STR_PAD_LEFT);

						if(!checkEntryTime($db, $rtbId, $datum, $roundedUhrzeit)){
							array_push($errors, 'Es ist bereits ein Eintrag mit dieser Uhrzeit vorhanden.');
						}

						if (ctype_space(htmlspecialchars($_POST['titel'])) || empty($_POST['titel'])) {
							array_push($errors, 'Der Titel darf nicht leer sein.');
						}

						if (ctype_space(htmlspecialchars($_POST['eintrag'])) || empty($_POST['eintrag'])) {
							array_push($errors, 'Der Eintrag darf nicht leer sein.');
						}

						if (isset($_POST['zusammenfassung']) && ($_POST['zusammenfassung'] == "1")) {
							$zusammenfassung = 1;
						} else {
							$zusammenfassung = 0;
						}

						if (isset($_POST['public']) && ($_POST['public'] == "1")) {
							$public = 1;
						} else {
							$public = 0;
						}

						if (isset($_POST['entwurf']) && ($_POST['entwurf'] == "1")) {
							$entwurf = 1;
						} else {
							$entwurf = 0;
						}

						$insertedPicsCount = 0;

						if(empty($errors)){
							for($i = 1; $i <= 3; $i++){
								if(isset($_POST['picture'.$i.'Id']) && $_POST['picture'.$i.'Id'] != "" && empty($errors)){
									if(!insertBild($db, $username, $_POST['picture'.$i.'Id'], $_POST['file'.$i.'_ext'], $i)) {
										array_push($errors, 'Ein Bild konnte nicht eingefügt werden.');
									} else {
										$insertedPicsCount++;
									}
								}
							}
						}

						if(empty($errors)){
							$insertEintrag = $db->prepare("INSERT INTO eintraege(reisetagebuch_id, titel, text, datum, uhrzeit, standort_id, entwurf, zusammenfassung, public) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
							$insertEintrag->execute(array($rtbId, htmlspecialchars($_POST['titel']), htmlspecialchars($_POST['eintrag']), $datum, $roundedUhrzeit, htmlspecialchars($_POST['standort']), $entwurf, $zusammenfassung, $public));
							$eintragId = $db->lastInsertId();
							for($i = 1; $i <= $insertedPicsCount; $i++){
								insertEintragBild($db, $username, $eintragId, $_POST['picture'.$i.'Id'], $_POST['bild'.$i.'unterschrift']);
							}
							echo "<script>window.location.href = 'reisetagebuecher.php?rtb=".$rtbUrl."&eintragErfolgreich=true';</script>";
						} else {
							cleanFolder($username);
							echo "<div class=\"uk-text-center\">";
								echo "<ul>";
								foreach($errors as $error){
									echo "<li>".$error."</li>";
								}
								echo "</ul>";
							echo "</div>";
						}
					} else {
						cleanFolder($username);
					}
					
				break;

				case 'eintrag':
					$formatiertesDatum = strftime("%e. %B %Y", strtotime($eintragsdatum));
					?>

					<div class="uk-flex uk-flex-column uk-flex-center uk-margin-top eintrag">
						<?php
						if(isOwner($db, $userId, $rtbId)){
							?>

							<div class="uk-margin uk-text-center">
								<a class="uk-icon-link uk-margin-left" uk-icon="icon: arrow-left; ratio: 1.2" href="reisetagebuecher.php?rtb=<?=$rtbUrl;?>">Zurück zum Reisetagebuch</a>
							</div>

							<div class="uk-text-center uk-text-lead" id="rtbTitel">
								<a class="uk-link-reset" href="reisetagebuecher.php?rtb=<?=$rtbUrl;?>"><?=$rtbTitel;?> <span class="uk-text-small">von <?=$username;?></span></a>
							</div>

							<div class="uk-margin uk-text-center">
								<span class="uk-h2"><?=$formatiertesDatum;?></span>
							</div>

							<div class="uk-margin uk-text-center">
								<form method="POST" action="eintraege.php?view=neu&datum=<?=$eintragsdatum;?>">
									<input type="text" name="rtb" value="<?=$rtbUrl;?>" hidden>
									<button class="uk-button uk-button-text" uk-tooltip="title: Neuer Eintrag; pos: bottom"><i uk-icon="plus"></i></button>
								</form>
							</div>
							<hr class="uk-width-1-1">  
							<?php 
							foreach($eintraege as $eintrag) {
								// Standort des Eintrages auswählen
								$standortName = '?';
								$eintragId = htmlspecialchars($eintrag['id']);
								
								if(isset($eintrag['standort_id'])){
									$selectStandort = $db->prepare("SELECT id, name FROM standorte WHERE id = ?");
									$standortId = htmlspecialchars($eintrag['standort_id']);
									$selectStandort->execute(array($standortId));
									$standort = $selectStandort->fetchAll(\PDO::FETCH_ASSOC);
									if(!empty($standort)){
										$standortName = $standort[0]['name'];
										$standortId = $standort[0]['id'];
									} 
								}

								?>
								<div class="uk-margin-top eintragHeader">
									<span class="uk-float-left">
										<?php
										if($eintrag['zusammenfassung'] != 1){
											$uhrzeit = substr_replace($eintrag['uhrzeit'], ':', 2, 0);
											echo $uhrzeit.", ";
										}
										?> 
										<span class="uk-text-lead"><?=$eintrag['titel'];?></span> 
										<?php if($eintrag['public'] == 1){
											echo "<i class=\"far fa-eye black\"></i>";
										} else {
											echo "<i class=\"far fa-eye-slash black\"></i>";
										}
										?>
									</span>
									<span class="uk-float-right">
										<?php 
										if($eintrag['zusammenfassung'] != 1){
											echo '<a href="standorte.php?id='.$standortId.'" class="uk-link-reset standortRechts"><i uk-icon="icon: location"></i> <i>'.$standortName.'</i></a>';
										}
										?>
										<a href="eintraege.php?view=bearbeiten&rtb=<?=$rtbUrl;?>&id=<?=$eintragId;?>" class="uk-icon-link uk-margin-left" uk-icon="icon: file-edit; ratio: 1.2"></a>
										<button id="deleteEintrag<?=$eintragId;?>" class="uk-icon-link uk-margin-left delete" uk-icon="icon: trash; ratio: 1.2"></button>
									</span>
									<span class="standortLinks uk-float-left">
										<?php 
										if($eintrag['zusammenfassung'] != 1){
											echo '<a href="standorte.php?id='.$standortId.'" class="uk-link-reset"><i uk-icon="icon: location"></i> <i>'.$standortName.'</i></a>';
										}
										?>
									</span>
								</div>
								<br/>
								<div class="uk-margin-top uk-text-break eintragText">
									<p><?=$eintrag['text'];?></p>
								</div>
								<?php
								$selectBilder = $db->prepare("SELECT bilder.id, bilder.file_ext FROM bilder JOIN eintraege_bilder ON (bilder.id = eintraege_bilder.bild_id) WHERE eintraege_bilder.eintrag_id = ?");
								$selectBilder->execute(array($eintragId));
								$bilder = $selectBilder->fetchAll(\PDO::FETCH_ASSOC);
								if(!empty($bilder)){
									foreach($bilder as $bild){
										echo '<img class="eintragBild uk-margin-small-bottom uk-border-rounded" src="/users/'.$username.'/'.$bild['id'].'.'.$bild['file_ext'].'"><br/>';
									}
								}
								?>
								<hr class="uk-width-1-1">
								<?php
							}

						} else { ?>

							<div class="uk-margin uk-text-center">
								<a class="uk-icon-link uk-margin-left" uk-icon="icon: arrow-left; ratio: 1.2" href="reisetagebuecher.php?rtb=<?=$rtbUrl;?>">Zurück zum Reisetagebuch</a>
							</div>
							
							<div class="uk-text-center uk-text-lead" id="rtbTitel">
								<a class="uk-link-reset" href="reisetagebuecher.php?rtb=<?=$rtbUrl;?>"><?=$rtbTitel;?> <span class="uk-text-small">von <?=$rtbCreator;?></span></a>
							</div>

							<div class="uk-margin uk-text-center">
								<span class="uk-h2"><?=$formatiertesDatum;?></span>
							</div>
							<hr class="uk-width-1-1">  

							<?php 
							$count = 0;
							foreach($eintraege as $eintrag) {
								$standortName = '?';
								if($eintrag['public'] == 1){
									// Standort des Eintrages auswählen
									$selectStandort = $db->prepare("SELECT name FROM standorte WHERE id = ?");
									$standortId = htmlspecialchars($eintrag['standort_id']);
									$selectStandort->execute(array($standortId));
									$standort = $selectStandort->fetchAll(\PDO::FETCH_ASSOC);
									if(!empty($standort)){
										$standortName = $standort[0]['name'];
									}
									?>
									<div class="uk-margin-top eintragHeader">
										<span class="uk-float-left">
											<?php
											if($eintrag['zusammenfassung'] != 1){
												$uhrzeit = substr_replace($eintrag['uhrzeit'], ':', 2, 0);
												echo $uhrzeit.", ";
											}
											?> 
											<span class="uk-text-lead"><?=$eintrag['titel'];?></span> 
										</span>
										<span class="uk-float-right standortRechts">
											<?php 
											if($eintrag['zusammenfassung'] != 1){
												echo '<a href="standorte.php?id='.$standortId.'" class="uk-link-reset"><i uk-icon="icon: location"></i> <i>'.$standortName.'</i></a>';
											}
											?>
										</span>
										<span class="uk-float-left standortLinks">
										<?php 
										if($eintrag['zusammenfassung'] != 1){
											echo '<a href="standorte.php?id='.$standortId.'" class="uk-link-reset"><i uk-icon="icon: location"></i> <i>'.$standortName.'</i></a>';
										}
										?>
										</span>
									</div>
									<br/>
									<div class="uk-margin-top uk-text-break eintragText">
										<p><?=$eintrag['text'];?></p>
									</div>
									<?php
									$selectBilder = $db->prepare("SELECT bilder.id, bilder.file_ext FROM bilder JOIN eintraege_bilder ON (bilder.id = eintraege_bilder.bild_id) WHERE eintraege_bilder.eintrag_id = ?");
									$eintragId = htmlspecialchars($eintrag['id']);
									$selectBilder->execute(array($eintragId));
									$bilder = $selectBilder->fetchAll(\PDO::FETCH_ASSOC);
									if(!empty($bilder)){
										foreach($bilder as $bild){
											echo '<img class="eintragBild uk-margin-small-bottom uk-border-rounded" src="/users/'.$rtbCreator.'/'.$bild['id'].'.'.$bild['file_ext'].'"><br/>';
										}
									}
									?>
									<hr class="uk-width-1-1">
									<?php
									$count++;
								}
							}

							if($count === 0){
								?>
								<div class="uk-margin-top uk-text-center">
									<span>Es gibt zu diesem Datum noch keine öffentliche Einträge.</span><br/>
								</div>
								<?php
							}
						} 
						?>
					</div>
				<?php
				break;

				case 'bearbeiten':
					$selectEintrag = $db->prepare("SELECT id, titel, text, datum, uhrzeit, standort_id, zusammenfassung, public FROM eintraege WHERE id = ?");
					$selectEintrag->execute(array($eintragId));
					$eintrag = $selectEintrag->fetchAll(\PDO::FETCH_ASSOC);

					$formatiertesDatum = strftime("%e. %B %Y", strtotime($eintrag[0]['datum']));
					// Gespeicherte Standorte des Benutzers 
					$selectStandorte = $db->prepare("SELECT id, name FROM standorte WHERE users_id = ? ORDER BY name");
					$selectStandorte->execute(array($userId));
					$standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);
					if(isset($rtbId) && isOwner($db, $userId, $rtbId) && !empty($eintrag)){
						?>
						<div class="uk-margin-top uk-margin-bottom">

							<div class="uk-text-center">
								<a class="uk-icon-link uk-margin-left" uk-icon="icon: arrow-left; ratio: 1.2" href="eintraege.php?rtb=<?=$rtbUrl;?>&datum=<?=$eintrag[0]['datum'];?>">Zurück zum Eintrag</a>
							</div>

							<h1 class="uk-text-center">Eintrag bearbeiten</h1>
							<hr class="uk-width-1-1">

							<!-- Modal um neue Standorte zu erstellen, geht auf wenn man "Neuer Standort" in der Selectbox auswählt -->
							<div id="standorteModal" class="uk-flex-top" uk-modal>
								<div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
									<button class="uk-modal-close-default" type="button" uk-close></button>
									<?php require '../include/neuerStandort.php'; ?>
								</div>
							</div>

							<form id="bearbeiten" method="POST">
								<fieldset class="uk-fieldset">

									<div class="uk-margin">
										<label>Zusammenfassung <input id="zusammenfassung" name="zusammenfassung" class="uk-checkbox" type="checkbox" value="1"></label>
									</div>

									<div class="uk-margin">
										<!-- Selectbox mit den gespeicherten Standorten des Benutzers -->
										<select id="standorte" class="uk-select uk-form-width-medium" name="standort">
											<option value="default" selected>Standort auswählen</option>
											<option value="neuer-standort" class="uk-text-bold">Neuer Standort</option>
											<?php
											$selected = ''; 
											foreach($standorte as $standort){
												if($standort['id'] == $eintrag[0]['standort_id']){
													$selected = 'selected';
												}
												echo "<option value=\"".$standort['id']."\"".$selected.">".$standort['name']."</option>";
											}
											?>
										</select>
									</div>

									<div class="uk-margin">
										<div class="uk-inline" id="dateInput">
											<span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar"></span>
											<?php
											$uhrzeit = substr_replace($eintrag[0]['uhrzeit'], ':', 2, 0);
											$dateTime = $eintrag[0]['datum']." ".$uhrzeit;
											?>
											<input type="text" name="dateTime" id="dateTime" value="<?=$dateTime;?>" class="uk-input uk-form-width-medium flatpickr" placeholder="Datum & Uhrzeit" required>
											<div id="uhrzeitError">
												<!-- Hier wird ein Fehler angezeigt, wenn es Bereits einen Eintrag mit der ausgewählten Uhrzeit gibt -->
											</div>
										</div>
									</div>

									<div class="uk-margin">
										<input name="titel" class="uk-input" type="text" placeholder="Titel..." value="<?=$eintrag[0]['titel'];?>" required>
									</div>

									<div class="uk-margin">
										<textarea name="eintrag" class="uk-textarea" rows="5" placeholder="Eintrag..." required><?=$eintrag[0]['text'];?></textarea>
									</div>

									<div class="uk-margin">
										<div id="eintragsBildUpload" class="js-upload uk-placeholder uk-text-center">
											<span uk-icon="icon: cloud-upload"></span>
											<span class="uk-text-middle">Bilder hochladen (max. 3, per Drag & Drop oder </span>
											<div uk-form-custom>
												<input type="file" name="files">
												<span class="uk-link">direkter Auswahl</span>)
											</div>
										</div>
										<progress id="js-progressbar3" class="uk-progress" value="0" max="100" hidden></progress>
										<div id="loading2" class="uk-text-center" hidden>
											<div uk-spinner></div>
											<span>Das Bild wird verarbeitet...</span>
										</div>
									</div>

									<div id="picturesError">
										<!-- Hier erscheinen die Fehler beim hochladen von Bildern -->
									</div>

									<div id="pictures" class="uk-margin uk-text-center uk-child-width-1-3" uk-grid>
										<?php
										$selectBilder = $db->prepare("SELECT bilder.id, bilder.file_ext FROM bilder JOIN eintraege_bilder ON (bilder.id = eintraege_bilder.bild_id) WHERE eintraege_bilder.eintrag_id = ?");
										$selectBilder->execute(array($eintragId));
										$bilder = $selectBilder->fetchAll(\PDO::FETCH_ASSOC);
										$anzahlBilder = 0;
										if(!empty($bilder)){
											$anzahlBilder = sizeof($bilder);
											$i = 1;
											foreach($bilder as $bild){
												echo '<div class="uk-inline uk-dark" id="picture'.$i.'Div"><button class="uk-position-top-right uk-icon-button deletePicture" type="button" uk-icon="icon: close"></button><img class="eintragBild uk-margin-small-bottom uk-border-rounded" src="/users/'.$rtbCreator.'/'.$bild['id'].'.'.$bild['file_ext'].'"></div>';
												echo "<input id=\"picture".$i."Id\" name=\"picture".$i."Id\" type=\"hidden\" value=\"".$bild['id']."\">";
												echo "<input id=\"file".$i."_ext\" name=\"file".$i."_ext\" type=\"hidden\" value=\"".$bild['file_ext']."\">";
												echo "<input id=\"bild".$i."unterschrift\" name=\"bild".$i."unterschrift\" type=\"hidden\" value=\"\">";
												$i++;
											}
										}

										for($i = $anzahlBilder + 1; $i <= 3; $i++){
											echo "<input id=\"picture".$i."Id\" name=\"picture".$i."Id\" type=\"hidden\" value=\"\">";
											echo "<input id=\"file".$i."_ext\" name=\"file".$i."_ext\" type=\"hidden\" value=\"\">";
											echo "<input id=\"bild".$i."unterschrift\" name=\"bild".$i."unterschrift\" type=\"hidden\" value=\"\">";
										}
										?>
									</div>

									<div class="uk-margin">
										<?php
										$checked = '';
										if($eintrag[0]['public'] == 1){
											$checked = 'checked';
										}
										?>
										<label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox" value="<?=$eintrag[0]['public'];?>" <?=$checked;?>></label>
									</div>

									<input id="rtbUrl" name="rtb" type="hidden" value="<?=$rtbUrl;?>">

								</fieldset>
								<div class="uk-flex uk-flex-center uk-flex-middle">
									<button class="uk-button uk-button-default" name="save">Speichern</button>
								</div>
							</form>
							<hr class="uk-width-1-1">
						</div>
					<?php
					} else {
						?>
						<div class="uk-margin-top uk-alert-danger" uk-alert>
							<p>Dieser Eintrag ist nicht vorhanden.</p>
						</div>
					<?php
					}

					// Formularverarbeitung 
					if(isset($_POST['save'], $_POST['standort'], $_POST['dateTime'], $_POST['titel'], $_POST['eintrag'])){
						$updateQuery = '';
						$errors = array();

						$datum = substr(htmlspecialchars($_POST['dateTime']), 0, 10);
						$uhrzeit = str_pad(str_replace(':', '', substr(htmlspecialchars($_POST['dateTime']), 11, 5)), 4, '0', STR_PAD_LEFT);
						$roundedUhrzeit = round((int) $uhrzeit / 5) * 5;
						$roundedUhrzeit = str_pad($roundedUhrzeit, 4, '0', STR_PAD_LEFT);

						if($roundedUhrzeit != $eintrag[0]['uhrzeit']){
							if(!checkEntryTime($db, $rtbId, $datum, $roundedUhrzeit)){
								array_push($errors, 'Es ist bereits ein Eintrag mit dieser Uhrzeit vorhanden.');
							}
						}

						if (ctype_space(htmlspecialchars($_POST['titel'])) || empty($_POST['titel'])) {
							array_push($errors, 'Der Titel darf nicht leer sein.');
						}

						if (ctype_space(htmlspecialchars($_POST['eintrag'])) || empty($_POST['eintrag'])) {
							array_push($errors, 'Der Eintrag darf nicht leer sein.');
						}

						if (isset($_POST['zusammenfassung']) && ($_POST['zusammenfassung'] == "1")) {
							$zusammenfassung = 1;
						} else {
							$zusammenfassung = 0;
						}

						if (isset($_POST['public'])) {
							$public = 1;
						} else {
							$public = 0;
						}

						$updateArray = array(
							$rtbId,
							htmlspecialchars($_POST['titel']), 
							htmlspecialchars($_POST['eintrag']),
							$datum,
							$roundedUhrzeit,
							htmlspecialchars($_POST['standort']), 
							$zusammenfassung, 
							$public,
							$eintragId,
							$rtbId
						);

						if(empty($errors)){
							for($i = $anzahlBilder + 1; $i <= 3; $i++){
								if(isset($_POST['picture'.$i.'Id']) && $_POST['picture'.$i.'Id'] != "" && empty($errors)){
									if(!insertBild($db, $username, $_POST['picture'.$i.'Id'], $_POST['file'.$i.'_ext'], $i)) {
										array_push($errors, 'Ein Bild konnte nicht eingefügt werden.');
									}
								}
							}
						}

						if(empty($errors)){
							$updateEintrag = $db->prepare("UPDATE eintraege SET reisetagebuch_id = ?, titel = ?, text = ?, datum = ?, uhrzeit = ?, standort_id = ?, zusammenfassung = ?, public = ? WHERE id = ? AND reisetagebuch_id = ?");
							$updateEintrag->execute($updateArray);
							for($i = $anzahlBilder + 1; $i <= 3; $i++){
								insertEintragBild($db, $username, $eintragId, $_POST['picture'.$i.'Id'], $_POST['bild'.$i.'unterschrift']);
							}
							echo "<script>window.location.href = 'reisetagebuecher.php?rtb=".$rtbUrl."&eintragErfolgreich=true';</script>";
						} else {
							cleanFolder($username);
							echo "<div class=\"uk-text-center\">";
								echo "<ul>";
								foreach($errors as $error){
									echo "<li>".$error."</li>";
								}
								echo "</ul>";
							echo "</div>";
						}
					} else {
						// Falls der Benutzer davor Bilder hochgeladen, aber nicht abgespeichert hat, werden diese gelöscht.
						cleanFolder($username);
					}
				break;

				default:
					require '../include/unavailable.php';
				break;
			}
		?>
		</div>
		<script src="/js/standortScript.js"></script>
		<script>
			<?php if(isset($username)){echo "var username = '$username';";} ?>
			var rtb = "<?php echo $rtbUrl; ?>";

			$(document.body).on('click', '.delete', function(){
				var eintragId = this.id.replace("deleteEintrag", "");
				UIkit.modal.confirm('Wollen Sie diesen Eintrag wirklich löschen ?').then(function() {
					$.ajax({
						url : '/ajax/deleteEintrag.php',
						type : 'POST',
						data : {
							eintragId: eintragId,
							rtb: rtb
						},
						success : function(response) {
							var response = JSON.parse(response);
							if(response.status == 'OK'){
								location.reload();
							} else if(response.status == 'ERROR'){
								UIkit.notification({message: 'Dieser Eintrag konnte nicht entfernt werden.', status: 'danger'});
							}
						}
					});
				}, function() {
					// Wenn der Benutzer auf "Cancel" drückt...
				});
			});

			// Initialisiert die Map beim öffnen des Modals
			$('#standorte').change(function () {
				var selectedOption = $(this).find("option:selected");
				var selectedValue = selectedOption.val();
				if(selectedValue == 'neuer-standort'){
					$("select option").prop("selected", false);
					UIkit.modal('#standorteModal').show();
					// Einstellungen des Locationpickers
					$('#pickerMap').locationpicker({
						location: {
							latitude: 49.609283,
							longitude: 6.551267
						},
						inputBinding: {
							latitudeInput: $('#lat'),
							longitudeInput: $('#lon'),
							locationNameInput: $('#locationInput')
						},
						addressFormat: 'street_address',
						enableAutocomplete: true,
						radius: null,
						oninitialized: function (component) {
							var fullAddress = $('#pickerMap').locationpicker('map').location.formattedAddress;
							$('#locationInput').val(fullAddress);
						}
					});
				}
			});

			$('#standortErstellen').on('click', function(){
				$('#standortErrors').empty();
				$.ajax({
					url : '/ajax/standorte_ajax.php',
					type : 'POST',
					data : {
						standortname : $('#standortname').val(),
						lat : $('#lat').val(),
						lon : $('#lon').val(),
						beschreibung : $('#standortBeschreibung').val(),
						pictureId : $('#pictureId').val(),
						file_ext : $('#file_ext').val()
					},
					success : function(response) {
						var response = JSON.parse(response);
						if(response.status == 'OK'){
							$('#standortname').val('');
							$('#standortBeschreibung').val('');
							$('#pictureId').val('');
							$('#file_ext').val('');
							$('#standortBild').empty();
							$('#standorte').empty().append(response.data);
							UIkit.notification({message: 'Ihr Standort wurde erfolgreich erstellt.', status: 'success'});
							UIkit.modal('#standorteModal').hide();
						} else if(response.status == 'ERROR') {
							$('#standortErrors').empty().append(response.data);
						}
					}
				});
			});

			// Wenn der Eintrag eine Zusammenfassung ist, kann man weder Standort noch Datum eingeben
			$('#zusammenfassung').change(function(){
				if($(this).is(':checked')){
					$('#standorte').hide();
					$('#dateInput').hide();
				}
				else {
					$('#standorte').show();
					$('#dateInput').show();
				}	
			});

			// Einstellungen des Datepickers
			$(".flatpickr").flatpickr({
				enableTime: true,
				altInput: true,
				altFormat: "j. F Y H:i",
				dateFormat: "Y-m-d H:i",
				/*minTime: "16:00",
				maxTime: "22:00",*/
				time_24hr: true,
				minuteIncrement: 5,
				onValueUpdate: function(dateStr, instance){
					var date = instance.substring(0, 10);
					var hours = instance.substring(11, 13);
					var minutes = instance.substring(14);
					var roundedMinutes = minutes - (minutes % 5);
					this.setDate(date+' '+hours+':'+roundedMinutes);
				}, 
				onClose: function(dateStr, instance){
					var date = instance.substring(0, 10);
					var hours = instance.substring(11, 13);
					var minutes = instance.substring(14);
					$.ajax({
						url : '/ajax/checkEntryTime.php',
						type : 'POST',
						data : {
							rtb : rtb,
							datum : date,
							uhrzeit : hours+''+minutes
						},
						success : function(response) {
							if(response != 'OK'){
								$('#uhrzeitError').empty().append('<div class="uk-margin-top uk-alert-danger" uk-alert><p>'+response+'</p></div>');
							} else {
								$('#uhrzeitError').empty();
							}
						}
					});
				}			  
			});

			var bar3 = document.getElementById('js-progressbar3');
			var anzahlBilder = "<?php if(isset($anzahlBilder)){echo $anzahlBilder;}else{echo 0;}?>";
			// Skript zum uploaden von Bildern zu einem Eintrag
			UIkit.upload('#eintragsBildUpload', {

				url: '/ajax/upload.php',
				mime: 'image/*',
				maxSize: 5000,
				method: 'POST',
				params: {
					multiple: true,
					anzahlBilder: anzahlBilder
				},

				beforeAll: function () {
					$('#eintragsBildUpload').hide();
					if($('#picture3Id').val() != ''){
						$('#picturesError').empty().append('<div class="uk-margin-top uk-alert-danger" uk-alert><p>Die maximale Anzahl (3) an Bildern wurde schon erreicht. Sie können andere Bilder ersetzen indem Sie diese davor löschen.</p></div>');
					}
				},

				fail: function (errorMsg) {
					UIkit.notification({message: errorMsg, status: 'danger'});
				},

				loadStart: function (e) {
					$('#loading2').removeAttr('hidden');
					bar3.removeAttribute('hidden');
					bar3.max = e.total;
					bar3.value = e.loaded;
				},

				progress: function (e) {
					bar3.max = e.total;
					bar3.value = e.loaded;
				},

				loadEnd: function (e) {
					bar3.max = e.total;
					bar3.value = e.loaded;
				},

				completeAll: function (data) {
					bar3.setAttribute('hidden', 'hidden');

					var infos = JSON.parse(data.response);
					if(infos.status == 'OK'){
						var fullPath = '../users/'+username+'/tmp'+infos.fieldToFill+'_'+infos.pictureId+'.'+infos.file_ext;

						$('#picture'+infos.fieldToFill+'Id').val(infos.pictureId);
						$('#file'+infos.fieldToFill+'_ext').val(infos.file_ext);
						$('#pictures').append('<div class="uk-animation-fade uk-inline uk-dark" id="picture'+infos.fieldToFill+'Div"><button class="uk-position-top-right uk-icon-button deletePicture" type="button" uk-icon="icon: close"></button><img class="uk-border-rounded eintragBild" data-src="'+fullPath+'" src="'+fullPath+'" uk-img></div>');
						UIkit.notification({message: 'Ihr Eintragsbild wurde erfolgreich hochgeladen.', status: 'success'});
					} else {
						$('#picturesError').empty().append('<div class="uk-margin-top uk-alert-danger" uk-alert><p>'+infos.msg+'</p></div>');
					}
					$('#eintragsBildUpload').show();
					$('#loading2').attr('hidden', 'hidden');
				}
			});

			$(document.body).on('click', '.deletePicture', function(){
				var pictureDiv = $(this).parent();
				UIkit.modal.confirm('Dieses Bild wirklich entfernen ?').then(function() {
					var picture = pictureDiv.find('.eintragBild').attr('src');
					$.ajax({
						url : '/ajax/removePicture.php',
						type : 'POST',
						data : {
							picture: picture
						},
						success : function(response) {
							var response = JSON.parse(response);
							if(response.status == 'OK'){
								if(response.picNum != 0){
									$('#picture'+response.picNum+'Id').val('');
									$('#file'+response.picNum+'_ext').val('');
									$('#bild'+response.picNum+'unterschrift').val('');
								}

								// Die IDs rearrangieren und die Bilder-divs löschen
								for($i = 0; $i <= 3; $i++){
									$next = $i+1;
									$('#picture'+$i+'Id').val($('#picture'+$next+'Id').val());
									$('#file'+$i+'_ext').val($('#file'+$next+'_ext').val());
									$('#bild'+$i+'unterschrift').val($('#bild'+$next+'unterschrift').val());
									$('#picture'+$i+'Div').remove();
								}

								// Die Bilder werden mit den neuen Werten wieder reingepackt
								$('#pictures').append(response.pictureDiv);
							} else if(response.status == 'ERROR'){
								$('#picturesError').empty().append('<div class="uk-margin-top uk-alert-danger" uk-alert><p>Das Bild konnte nicht entfernt werden.</p></div>');
							}
						}
					});
				}, function() {
					// Wenn der Benutzer auf "Cancel" drückt...
				});
			});
		</script>
	</body>
</html> 