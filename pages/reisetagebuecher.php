<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_GET["view"])) {
		$view = htmlspecialchars($_GET["view"]);
	} elseif(isset($_POST["view"])) {
		$view = htmlspecialchars($_POST["view"]);
	} else {
		$view = "meine";
	}

	if(isset($_POST['rtbId'])){
		$rtbId = htmlspecialchars($_POST['rtbId']);
	} elseif(isset($_GET['rtbId'])){
		$rtbId = htmlspecialchars($_POST['rtbId']);
	}

	if(isset($_POST['rtb'])){
		$rtbUrl = htmlspecialchars($_POST['rtb']);
	} elseif(isset($_GET['rtb'])){
		$view = "reisetagebuch";
		$rtbUrl = htmlspecialchars($_GET['rtb']);
	}

	$onlyLogged = array('meine', 'neu', 'bearbeiten');
	checkAuthorization($userId, $view, $onlyLogged);

?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Reisetagebücher</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			
			<?php 
			if(isset($rtbUrl) && !empty($rtbUrl)){
				$rtbId = getRtbIdFromUrl($db, $rtbUrl);
				$selectReisetagebuchDaten = $db->prepare("SELECT reisetagebuecher.id, users.username, titel, url, beschreibung, public, erstellt_am, bild_id, bilder.file_ext FROM reisetagebuecher LEFT JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) JOIN users ON (users_id = users.id) WHERE reisetagebuecher.id = ?");
				$selectReisetagebuchDaten->execute(array($rtbId));
				$reisetagebuchDaten = $selectReisetagebuchDaten->fetchAll(\PDO::FETCH_ASSOC);

				// Ist das Reisetagebuch nicht vorhanden oder nicht öffentlich und von einem anderen Benutzer, wird man zum default case weitergeleitet (nicht vorhandene Seite)
				if(empty($reisetagebuchDaten) || ($reisetagebuchDaten[0]['public'] != 1 && !isOwner($db, $userId, $rtbId))){
					$view = 'not_available';
				}
			}

			switch ($view) {
				case 'meine':	
				// Fügt die Reisetagebücher des Benutzers in ein Array
				$selectReisetagebuecher = $db->prepare("SELECT titel, beschreibung, url, public, erstellt_am, bild_id, bilder.file_ext FROM reisetagebuecher LEFT JOIN bilder ON (reisetagebuecher.bild_id = bilder.id) WHERE users_id = ?");
				$selectReisetagebuecher->execute(array($userId));
				$reisetagebuecher = $selectReisetagebuecher->fetchAll(\PDO::FETCH_ASSOC);
				if(!empty($reisetagebuecher)){
					?>
					<div class="uk-child-width-1-3@l uk-child-width-1-1@s uk-margin-top uk-margin-bottom uk-text-center" uk-grid>
					<?php
					foreach($reisetagebuecher as $reisetagebuch){
					?>
						<div>
						<?php
						echo "<div class=\"uk-card uk-card-default uk-card-hover uk-animation-toggle uk-height-large rtbCard\" onclick=\"document.location='reisetagebuecher.php?rtb=".$reisetagebuch['url']."'\">"; ?>
								<div class="uk-card-badge uk-label">
									<?php if($reisetagebuch['public'] == 1){
										echo "<i class=\"far fa-eye\"></i>";
									} else {
										echo "<i class=\"far fa-eye-slash\"></i>";
									}
									?>
								</div>
								<?php 
									if(!empty($reisetagebuch['bild_id'])){
										echo '<img class="titelbild" src="/users/'.$username.'/'.$reisetagebuch['bild_id'].'.'.$reisetagebuch['file_ext'].'">';
									} else {
										echo '<img class="titelbild" src="/pictures/no-picture.jpg">';
									} 
								?>
								<div class="uk-overlay uk-overlay-default uk-position-bottom">
									<span class="uk-h2"><?=$reisetagebuch['titel'];?></span>
									<p><?=$reisetagebuch['beschreibung'];?></p>
									<span class="uk-text-small uk-float-right"><i>erstellt am <?=getMySqlDate($reisetagebuch['erstellt_am']);?></i></span><br/>
								</div>   
							</div>
						</div>
					<?php
					}
					?>
					</div>
				<?php
				} else {
					?>
					<div class="uk-margin-top uk-text-center">
						<span>Sie haben noch kein Reisetagebuch angelegt.</span><br/>
						<button class="uk-button uk-button-text uk-text-uppercase"><a class="uk-heading uk-link-reset newRtbLink" href="reisetagebuecher.php?view=neu">Neues Reisetagebuch anlegen</a></button>
					</div>
				<?php
				}

			break;

			case 'neu':
			?>
			<div class="uk-margin-top uk-margin-bottom">
				<h1 class="uk-text-center">Neues Reisetagebuch</h1>
				<hr class="uk-width-1-1">

				<div id="titelbild" class="uk-margin uk-text-center">
					<!-- Hier erscheint das Titelbild sobald eins hochgeladen wird -->
				</div>

				<form id="neu" method="POST">
					<fieldset class="uk-fieldset">

						<div class="uk-margin">
							<i><span id="char_count">25</span> verbleibend</i>
							<input name="titel" id="titel" class="uk-input" type="text" placeholder="Titel (maximal 25 Zeichen)" onFocus="countChars('titel','char_count',25)" onKeyDown="countChars('titel','char_count',25)" onKeyUp="countChars('titel','char_count',25)" maxlength="25" required>
						</div>

						<div class="uk-margin">
							<textarea name="beschreibung" class="uk-textarea" rows="5" type="text" placeholder="Beschreibung..." required></textarea>
						</div>

						<div class="uk-margin">
							<label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox" value="1"></label>
						</div>
						
						<div class="uk-margin">
							<div class="js-upload uk-placeholder uk-text-center">
								<span uk-icon="icon: cloud-upload"></span>
								<span class="uk-text-middle">Titelbild hochladen (per Drag & Drop oder </span>
								<div uk-form-custom>
									<input type="file" name="files">
									<span class="uk-link">direkter Auswahl</span>)
								</div>
							</div>
							<progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
						</div>

						<div id="loading" class="uk-text-center" hidden>
							<div uk-spinner></div>
							<span>Das Bild wird verarbeitet...</span>
						</div>

						<input id="pictureId" name="pictureId" type="hidden" value="">
						<input id="file_ext" name="file_ext" type="hidden" value="">

					</fieldset>
					<div class="uk-flex uk-flex-center uk-flex-middle">
						<button class="uk-button uk-button-default" name="create">Erstellen</button>
					</div>
				</form>
				<hr class="uk-width-1-1">
			<?php 
			// Formularverarbeitung 
			if(isset($_POST['create'])){
				$errors = array();
				if (ctype_space(htmlspecialchars($_POST['titel'])) || empty($_POST['titel'])) {
					array_push($errors, 'Der Titel darf nicht leer sein.');
				}

				if (ctype_space(htmlspecialchars($_POST['beschreibung'])) || empty($_POST['beschreibung'])) {
					array_push($errors, 'Die Beschreibung darf nicht leer sein.');
				}

				if (isset($_POST['public']) && ($_POST['public'] == "1")) {
					$public = 1;
				} else {
					$public = 0;
				}

				if($_POST['pictureId'] != "" && empty($errors)){
					if(!insertBild($db, $username, $_POST['pictureId'], $_POST['file_ext'], null)) {
						array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
					}
				}

				if(empty($errors)){
					$uniqueUrl = uniqueDbId($db, 'reisetagebuecher', 'url');
					$insertReisetagebuch = $db->prepare("INSERT INTO reisetagebuecher(users_id, titel, beschreibung, url, public, bild_id) VALUES(?, ?, ?, ?, ?, ?)");
					$insertReisetagebuch->execute(array(htmlspecialchars($userId), htmlspecialchars($_POST['titel']), htmlspecialchars($_POST['beschreibung']), $uniqueUrl, $public, htmlspecialchars($_POST['pictureId'])));
					echo "<script>window.location.href = 'reisetagebuecher.php?view=meine&success=true';</script>";
				} else {
					echo "<ul>";
					foreach($errors as $error){
						echo "<li>".$error."</li>";
					}
					echo "</ul>";
				}
			}
			?>
			</div>
			<?php
			break;

			case 'reisetagebuch':
			
			?>
			<div id="shareModal" uk-modal>
			    <div class="uk-modal-dialog">
			    	<div class="uk-modal-body">
				        <h2 class="uk-modal-title">"<?=$reisetagebuchDaten[0]['titel'];?>" teilen</h2>
				        <a class="uk-icon-button shareIcon" uk-icon="icon: copy; ratio: 2" uk-tooltip="title: Link kopieren; pos: bottom" id="copyUrl"></a>
						<a class="uk-icon-button shareIcon" uk-icon="icon: mail; ratio: 2" uk-tooltip="title: E-Mail; pos: bottom" id="email"></a>
						<a class="uk-icon-button shareIcon" uk-tooltip="title: PDF erstellen; pos: bottom" id="pdf"><i class="far fa-file-pdf fa-2x"></i></a>
						<a class="uk-icon-button shareIcon" uk-icon="icon: facebook; ratio: 2" uk-tooltip="title: Auf Facebook teilen; pos: bottom" id="facebook"></a>
						<a class="uk-icon-button shareIcon" uk-icon="icon: whatsapp; ratio: 2" uk-tooltip="title: Auf Whatsapp teilen; pos: bottom" id="whatsapp"></a>
						<a class="uk-icon-button shareIcon" uk-icon="icon: twitter; ratio: 2" uk-tooltip="title: Tweeten; pos: bottom" id="twitter"></a>
					</div>
					<div class="uk-modal-footer uk-text-right">
			            <button class="uk-button uk-button-default uk-modal-close" type="button">Schließen</button>
			        </div>
			    </div>
			</div>

			<div class="uk-flex uk-flex-center uk-flex-column uk-flex-middle">
				<div class="uk-margin-top uk-margin-bottom">
				<?php 
				$selectDates = $db->prepare("SELECT DISTINCT datum FROM eintraege WHERE reisetagebuch_id = ? AND entwurf = 0 ORDER BY datum DESC");
				$selectDates->execute(array($rtbId));
				$dates = $selectDates->fetchAll(\PDO::FETCH_ASSOC);
				if(isOwner($db, $userId, $rtbId)){
				?>
					<div>
						<div>
							<form method="POST" action="reisetagebuecher.php?view=bearbeiten">
								<input type="text" name="rtb" value="<?=$rtbUrl;?>" hidden>
								<button class="uk-icon-link uk-float-left" name="reisetagebuch-bearbeiten" uk-icon="icon: file-edit; ratio: 1.5"></button>
							</form>
						</div>
						<div><button id="share" class="uk-icon-link uk-float-right" name="share" uk-icon="icon: social; ratio: 1.5"></button></div>
						<div><a href="landkarte.php?rtb=<?=$rtbUrl;?>" class="uk-icon-link uk-float-right far fa-map fa-big uk-margin-small-right"></a></div>
						<div class="uk-text-center uk-text-lead" id="rtbTitel"><?=$reisetagebuchDaten[0]['titel'];?> <span class="uk-text-small">von <?=$username;?></span></div>
					</div>

					<div id="titelbild" class="uk-margin uk-text-center">
						<?php 
						if(!empty($reisetagebuchDaten[0]['bild_id'])){
							echo '<img data-src="../users/'.$username.'/'.$reisetagebuchDaten[0]['bild_id'].'.'.$reisetagebuchDaten[0]['file_ext'].'" uk-img class="uk-border-rounded">'; 
						} else {
							echo '<img class="uk-border-rounded" data-src="/pictures/no-picture.jpg" uk-img>';
						} 
						?>
					</div>					
					<?php 

					if(!empty($dates)){
					?>
						<table class="uk-table uk-table-hover uk-table-justify uk-table-divider">
							<thead>
								<tr>
									<th class="uk-text-center">Einträge (<?=sizeof($dates);?>)</th>
									<th class="uk-text-right">
										<form method="POST" action="eintraege.php?view=neu">
											<input type="text" name="rtb" value="<?=$rtbUrl;?>" hidden>
											<button class="uk-button uk-button-text" name="neu"><i uk-icon="plus"></i> Neuer Eintrag</button>
										</form>
									</th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach($dates as $datum){
									$selectEintraege = $db->prepare("SELECT titel FROM eintraege WHERE reisetagebuch_id = ? AND datum = ? AND entwurf = 0");
									$selectEintraege->execute(array($rtbId, $datum['datum']));
									$eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);

									$formatiertesDatum = strftime("%e. %B %Y", strtotime($datum['datum']));
									?>
									<tr class="eintragBox" onclick="document.location='eintraege.php?rtb=<?=$rtbUrl;?>&datum=<?=$datum['datum'];?>'">
										<td>
										<span class="uk-text-bold"><?=$formatiertesDatum;?> </span>
										<i>
										<?php
										$anzahlEintraege = sizeof($eintraege);
										for($i = 0; $i <= $anzahlEintraege - 1; $i++){
											echo $eintraege[$i]['titel'];

											if($i < $anzahlEintraege - 1){
												echo ", ";
											}

											if($i >= 2){
												echo "...";
												break;
											}
										}

										?>
										</i>	
										</td>
										<td class="uk-text-right">
											<?php 
											echo $anzahlEintraege;
											if($anzahlEintraege === 1){
												echo " EINTRAG";
											} else {
												echo " EINTRÄGE"; 
											}
											?>
										</td>
									</tr>
								<?php
								}
								?>
							</tbody>
						</table>
					<?php
					} else {
						?>
						<div class="uk-margin-top uk-text-center">
							<span>Sie haben zu diesem Reisetagebuch noch keinen Eintrag geschrieben.</span><br/>
							<form method="POST" action="eintraege.php?view=neu">
								<input type="text" name="rtb" value="<?=$rtbUrl;?>" hidden>
								<button class="uk-button uk-button-text" name="neu">Neuer Eintrag</button>
							</form>
						</div>
					<?php
					}
				} else {
					?>
					<div>
						<div><button id="share" class="uk-icon-link uk-float-right" name="share" uk-icon="icon: social; ratio: 1.5"></button></div>
						<div><a href="landkarte.php?rtb=<?=$rtbUrl;?>" class="uk-icon-link uk-float-right far fa-map fa-big uk-margin-small-right"></a></div>
						<div class="uk-text-center uk-text-lead" id="rtbTitel"><?=$reisetagebuchDaten[0]['titel'];?> <span class="uk-text-small">von <?=$reisetagebuchDaten[0]['username'];?></span></div>
					</div>

					<div id="titelbild" class="uk-margin uk-text-center">
						<?php 
						if(!empty($reisetagebuchDaten[0]['bild_id'])){
							echo '<img class="uk-border-rounded" data-src="../users/'.$reisetagebuchDaten[0]['username'].'/'.$reisetagebuchDaten[0]['bild_id'].'.'.$reisetagebuchDaten[0]['file_ext'].'" uk-img>'; 
						} else {
							echo '<img class="uk-border-rounded" data-src="/pictures/no-picture.jpg" uk-img>';
						} 
						?>
					</div>					
					<?php 
					if(!empty($dates)){
					?>
						<table class="uk-table uk-table-hover uk-table-justify uk-table-divider">
							<thead>
								<tr>
									<th class="uk-text-center">Einträge</th>
								</tr>
							</thead>
							<tbody>
								<?php
									foreach($dates as $datum){
										$selectEintraege = $db->prepare("SELECT titel FROM eintraege WHERE reisetagebuch_id = ? AND datum = ?");
										$selectEintraege->execute(array($rtbId, $datum['datum']));
										$eintraege = $selectEintraege->fetchAll(\PDO::FETCH_ASSOC);

										$formatiertesDatum = strftime("%e. %B %Y", strtotime($datum['datum']));
										?>
										<tr class="eintragBox" onclick="document.location='eintraege.php?rtb=<?=$rtbUrl;?>&datum=<?=$datum['datum'];?>'">
											<td>
												<span class="uk-text-bold"><?=$formatiertesDatum;?> </span>
												<i>
												<?php
												foreach($eintraege as $eintrag){
													echo $eintrag['titel'].", ";
												}
												?>
												...
												</i>	
											</td>
										</tr>
									<?php
									}
									?>
							</tbody>
						</table>
					<?php
					} else {
						?>
						<div class="uk-margin-top uk-text-center">
							<span>Es wurde zu diesem Reisetagebuch noch nichts eingetragen.</span><br/>
						</div>
						<?php
					}
					?>
				<?php
				}
				?>
				</div>
			</div>
			<?php 
			break;

			case 'bearbeiten':
				?>
				<div class="uk-flex uk-flex-center uk-flex-column uk-flex-middle">
					<div class="uk-margin-top uk-margin-bottom">
						<h1 class="uk-text-center">Reisetagebuch bearbeiten</h1>
						<hr class="uk-width-1-1">

						<div id="titelbild" class="uk-margin uk-text-center">
						<?php 
							$pictureId = '';
							$file_ext = '';
							if(!empty($reisetagebuchDaten[0]['bild_id'])){
								$pictureId = $reisetagebuchDaten[0]['bild_id'];
								$file_ext = $reisetagebuchDaten[0]['file_ext'];
								echo '<img class="titelbild uk-border-rounded" src="/users/'.$username.'/'.$pictureId.'.'.$file_ext.'">';
							} else {
								echo '<img class="titelbild uk-border-rounded" src="/pictures/no-picture.jpg">';
							} 
						?>
						</div>

						<form id="bearbeiten" method="POST">
							<fieldset class="uk-fieldset">

								<div class="uk-margin">
									<div class="js-upload uk-placeholder uk-text-center">
										<span uk-icon="icon: cloud-upload"></span>
										<span class="uk-text-middle">Titelbild ersetzen (per Drag & Drop oder </span>
										<div uk-form-custom>
											<input type="file" name="files">
											<span class="uk-link">direkter Auswahl</span>)
										</div>
									</div>
									<progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
								</div>

								<div id="loading" class="uk-text-center" hidden>
									<div uk-spinner></div>
									<span>Das Bild wird verarbeitet...</span>
								</div>

								<div class="uk-margin">
									<i><span id="char_count"><?= 26 - strlen($reisetagebuchDaten[0]['titel']);?></span> verbleibend</i>
									<input name="titel" id="titel" class="uk-input" type="text" placeholder="Titel (maximal 25 Zeichen)" onFocus="countChars('titel','char_count',25)" onKeyDown="countChars('titel','char_count',25)" onKeyUp="countChars('titel','char_count',25)" maxlength="25" value="<?=$reisetagebuchDaten[0]['titel'];?>" required>
								</div>

								<div class="uk-margin">
									<textarea name="beschreibung" class="uk-textarea" rows="5" type="text" placeholder="Beschreibung..." required><?=$reisetagebuchDaten[0]['beschreibung'];?></textarea>
								</div>

								<div class="uk-margin">
									<?php
									$checked = '';
									if($reisetagebuchDaten[0]['public'] == 1){
										$checked = 'checked';
									}
									?>
									<label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox" value="<?=$reisetagebuchDaten[0]['public'];?>" <?=$checked;?>></label>
								</div>

								<div class="uk-margin">
									<button type="button" id="deleteReisetagebuch<?=$rtbUrl;?>" class="uk-icon-link delete" uk-icon="icon: trash;"><span class="red uk-text-uppercase">Reisetagebuch löschen</span></button>
								</div>

								<input id="pictureId" name="pictureId" type="hidden" value="">
								<input id="file_ext" name="file_ext" type="hidden" value="">
								<input name="rtb" type="hidden" value="<?=$rtbUrl;?>">

							</fieldset>
							<div class="uk-flex uk-flex-center uk-flex-middle">
								<button type="submit" class="uk-button uk-button-default" name="save">Speichern</button>
							</div>
						</form>
						<hr class="uk-width-1-1">
					<?php 
					// Formularverarbeitung 
					if(isset($_POST['save'])){
						$errors = array();
						$updateQuery = '';

						if (ctype_space(htmlspecialchars($_POST['titel'])) || empty($_POST['titel'])) {
							array_push($errors, 'Der Titel darf nicht leer sein.');
						}

						if (ctype_space(htmlspecialchars($_POST['beschreibung'])) || empty($_POST['beschreibung'])) {
							array_push($errors, 'Die Beschreibung darf nicht leer sein.');
						}

						if (isset($_POST['public'])) {
							$public = 1;
						} else {
							$public = 0;
						}

						$updateArray = array(
							htmlspecialchars($_POST['titel']), 
							htmlspecialchars($_POST['beschreibung']), 
							$public
						);

						if(isset($_POST['pictureId']) && $_POST['file_ext'] && !empty($_POST['pictureId']) && !empty($_POST['file_ext']) && empty($errors)){
							if(!empty($pictureId) && !empty($file_ext)){
								// Ersetzen des vorherigen Bildes
								$pictureError = updateBild($db, $username, $pictureId, htmlspecialchars($_POST['pictureId']), $file_ext, htmlspecialchars($_POST['file_ext']));
							} else {
								// War noch kein Bild vorhanden, wird es eingefügt
								$pictureError = insertBild($db, $username, htmlspecialchars($_POST['pictureId']), htmlspecialchars($_POST['file_ext']));
							}

							if(!$pictureError){
								array_push($errors, 'Das Bild konnte nicht ersetzt werden.');
							} else {
								$updateQuery .= ', bild_id = ?';
								array_push($updateArray, htmlspecialchars($_POST['pictureId']));
							}
						}

						array_push($updateArray, $rtbId);

						if(empty($errors)){
							$updateReisetagebuch = $db->prepare("UPDATE reisetagebuecher SET titel = ?, beschreibung = ?, public = ?".$updateQuery." WHERE id = ?");
							$updateReisetagebuch->execute($updateArray);
							echo "<script>window.location.href = 'reisetagebuecher.php?view=meine&success=true';</script>";
						} else {
							echo "<ul>";
							foreach($errors as $error){
								echo "<li>".$error."</li>";
							}
							echo "</ul>";
						}
					}
					?>
					</div>
				</div>
				<?php
			break;

			default:
				require '../include/unavailable.php';
			break;
			}
		?>
		</div>
		<?php 
			// Success Benachrichtigungen 
			if(isset($_GET['login'])){echo "<script>UIkit.notification({message: 'Sie sind angemeldet.', status: 'success', pos: 'top-right'});</script>";}
			if(isset($_GET['success'])){echo "<script>UIkit.notification({message: 'Ihr Reisetagebuch wurde erfolgreich gespeichert.', status: 'success', pos: 'top-right'});</script>";}
			if(isset($_GET['eintragErfolgreich'])){echo "<script>UIkit.notification({message: 'Ihr Eintrag wurde erfolgreich gespeichert.', status: 'success', pos: 'top-right'});</script>";}
		?>
		<script>
			var bar = document.getElementById('js-progressbar');
			<?php 
				if(isset($username)){
					echo 'var username = "'.$username.'";'; 
				}
				if(isset($rtbUrl)){
					echo 'var rtb = "'.$rtbUrl.'";'; 
				}
			?>

			// Skript zum uploaden von Bildern
			UIkit.upload('.js-upload', {

				url: '/include/upload.php',
				multiple: false,
				mime: 'image/*',
				maxSize: 5000,
				method: 'POST',

				beforeAll: function () {
					$('.js-upload').hide();
				},

				fail: function (errorMsg) {
					UIkit.notification.closeAll();
					UIkit.notification({message: errorMsg, status: 'danger'});
				},

				loadStart: function (e) {
					$('#loading').removeAttr('hidden');
					bar.removeAttribute('hidden');
					bar.max = e.total;
					bar.value = e.loaded;
				},

				progress: function (e) {
					bar.max = e.total;
					bar.value = e.loaded;
				},

				loadEnd: function (e) {
					bar.max = e.total;
					bar.value = e.loaded;
				},

				completeAll: function (data) {
					bar.setAttribute('hidden', 'hidden');

					var infos = JSON.parse(data.response);
					var fullPath = '../users/'+username+'/tmp_'+infos.pictureId+'.'+infos.file_ext;

					$('#pictureId').val(infos.pictureId);
					$('#file_ext').val(infos.file_ext);
					$('#titelbild').empty().append('<div class="uk-animation-fade"><img class="uk-border-rounded" data-src="'+fullPath+'" uk-img></div>');
					UIkit.notification.closeAll();
					UIkit.notification({message: 'Ihr Titelbild wurde erfolgreich hochgeladen.', status: 'success'});
					$('.js-upload').show();
					$('#loading').attr('hidden', 'hidden');
				}
			});

			$(document.body).on('click', '.delete', function(){
				var rtb = this.id.replace("deleteReisetagebuch", "");
				UIkit.modal.confirm('Wollen Sie dieses Reisetagebuch wirklich löschen ?').then(function() {
					$.ajax({
						url : '/ajax/deleteReisetagebuch.php',
						type : 'POST',
						data : {
							rtb: rtb
						},
						success : function(response) {
							var response = JSON.parse(response);
							if(response.status == 'OK'){
								window.location.href="reisetagebuecher.php?view=meine";
							} else if(response.status == 'ERROR'){
								UIkit.notification({message: 'Dieses Reisetagebuch konnte nicht entfernt werden.', status: 'danger'});
							}
						}
					});
				}, function() {
					// Wenn der Benutzer auf "Cancel" drückt...
				});
			});

			$(document.body).on('click', '#share', function(){
				$.ajax({
					url : '/ajax/getLinkQrCode.php',
					type : 'POST',
					data : {
						url: rtb
					},
					success : function(response) {
						$('#shareModal').find('.uk-modal-body').append(response);
					}
				});
				UIkit.modal('#shareModal').show();
			});

			$(document.body).on('click', '.shareIcon', function(){
				var action = this.id;
				var url = window.location.href;
				var body = 'Ich habe ein interessantes Reisetagebuch auf journuit gefunden. Schau es dir mal an : '+url+' !';

				if(action == 'copyUrl'){
					var urlInput = document.createElement('input');
				    
					document.body.appendChild(urlInput);
					urlInput.value = url;
					urlInput.select();
					document.execCommand('copy');
					document.body.removeChild(urlInput);
					UIkit.notification.closeAll()
					UIkit.notification({message: 'Der Link wurde kopiert!', status: 'success', pos: 'bottom-center'});
				}

				if(action == 'email'){
					var subject = '<?=$reisetagebuchDaten[0]['titel'].', von '.$reisetagebuchDaten[0]['username'];?>';
					window.location.href = "mailto:?subject="+subject+"&body="+body;
				}

				if(action == 'facebook'){
					window.open("https://www.facebook.com/sharer/sharer.php?u="+url);
				}

				if(action == 'whatsapp'){
					window.open("https://wa.me/?text="+body);
				}

				if(action == 'twitter'){
					window.open("https://twitter.com/intent/tweet?text="+body);
				}
			});

		</script>
	</body>
</html>