<?php
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
	require $_SERVER['DOCUMENT_ROOT'].'/include/functions.php';

	if(isset($_GET["view"])) {
		$view = htmlspecialchars($_GET["view"]);
	} elseif(isset($_POST["view"])) {
		$view = htmlspecialchars($_POST["view"]);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Neuer Eintrag</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<?php 
			switch ($view) {
				case 'neuer-eintrag':

				// Gespeicherte Standorte des Benutzers 
				$id = $auth->getUserId();
                $selectStandorte = $db->prepare("SELECT id, name FROM standorte WHERE users_id = ?");
                $selectStandorte->execute(array($id));
                $standorte = $selectStandorte->fetchAll(\PDO::FETCH_ASSOC);
                if(isset($_POST['rtbId']) && !empty($_POST['rtbId'])){
                	$rtbId = htmlspecialchars($_POST['rtbId']);
                	$_SESSION['rtbId'] = $rtbId;

                	$selectRtbTitel = $db->prepare("SELECT titel FROM reisetagebuecher WHERE id = ?");
		            $selectRtbTitel->execute(array($rtbId));
		            $rtbTitel = $selectRtbTitel->fetchAll(\PDO::FETCH_ASSOC);
		            $rtbTitel = $rtbTitel[0]['titel'];
                } else {
                	?>
                	<div class="uk-margin-top uk-alert-danger" uk-alert>
					    <p>Dieses Reisetagebuch ist nicht vorhanden.</p>
					</div>
				<?php
					break;
                }
				?>
				<div class="uk-margin-top uk-margin-bottom">
					<h1 class="uk-text-center">Neuer Eintrag</h1>
					<h2 class="uk-text-center uk-margin-remove-top"><?=$rtbTitel;?></h2>
					<hr class="uk-width-1-1">

					<form id="neuer-eintrag" method="POST">
					    <fieldset class="uk-fieldset">

					    	<div class="uk-margin">
						    	<label>Zusammenfassung <input name="zusammenfassung" class="uk-checkbox" type="checkbox" value="1"></label>
					        </div>

					        <div id="standorteModal" class="uk-flex-top" uk-modal>
							    <div class="uk-modal-dialog uk-margin-auto-vertical">
							    	<button class="uk-modal-close-default" type="button" uk-close></button>
							    </div>
							</div>

					        <div class="uk-margin">
					        	<!-- Selectbox mit den gespeicherten Standorten des Benutzers -->
					        	<select id="standorte" class="uk-select uk-form-width-medium" name="standort">
					        		<option value="default" selected disabled hidden>Standort auswählen</option>
					        		<option value="neuer-standort" class="uk-text-bold">Neuer Standort</option>
					        		<optgroup label="Meine Standorte">
						        		<?php 
						        		foreach($standorte as $standort){
						        			echo "<option value=\"".$standort['id']."\">".$standort['name']."</option>";
						        		}
						        		?>
						        	</optgroup>
					        	</select>
					        </div>

					        <div class="uk-margin">
					        	<div class="uk-inline">
	    							<span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: calendar"></span>
						        	<input type="text" name="dateTime" class="uk-input uk-form-width-medium flatpickr" placeholder="Datum & Uhrzeit" required>
					        	</div>
					        </div>

					        <div class="uk-margin">
						        <input name="titel" class="uk-input" type="text" placeholder="Titel..." required>
					        </div>

					        <div class="uk-margin">
						        <textarea name="eintrag" class="uk-textarea" rows="5" placeholder="Eintrag..." required></textarea>
					        </div>

					        <div class="uk-margin">
						    	<div class="js-upload uk-placeholder uk-text-center">
						    		<span uk-icon="icon: cloud-upload"></span>
								    <span class="uk-text-middle">Bilder hochladen (max. 3, per Drag & Drop oder </span>
								    <div uk-form-custom>
								        <input type="file" name="files">
								        <!-- Dateigröße auf 5MB limitieren -->
								        <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
								        <span class="uk-link">direkter Auswahl</span>)
								    </div>
								</div>
								<progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
					        </div>

					        <div class="uk-margin">
						    	<label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox" value="1"></label>
					        </div>
					        
					        <div id="bilder" class="uk-margin uk-text-center">
			        		<!-- Hier erscheinen die hochgeladene Bilder-->
			        		</div>

			        		<input type="hidden" name="rtbId" value="<?=$rtbId;?>" />

					    </fieldset>
					    <div class="uk-flex uk-flex-center uk-flex-middle">
					    	<button class="uk-button uk-button-default uk-margin-right" name="entwurf">Als Entwurf speichern</button>
					    	<button class="uk-button uk-button-default" name="create">Erstellen</button>
					    </div>
					</form>
					<hr class="uk-width-1-1">
				</div>
				<?php 
				// Formularverarbeitung 
				if(isset($_POST['create'], $_POST['standort'], $_POST['dateTime'], $_POST['titel'], $_POST['eintrag'])){
					$errors = array();
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

					if($_POST['pictureId'] != "" && empty($errors)){
						if(!insertBild($db, $username, $_POST['pictureId'], $_POST['file_ext'])) {
							array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
						}
					}

					if(empty($errors)){
						$userId = $auth->getUserId();
						$insertEintrag = $db->prepare("INSERT INTO eintraege(reisetagebuch_id, titel, text, datum, uhrzeit, standort_id, entwurf, zusammenfassung, public) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
						$insertEintrag->execute(array($rtbId, htmlspecialchars($_POST['titel']), htmlspecialchars($_POST['eintrag']), $datum, $uhrzeit, htmlspecialchars($_POST['standort']), $entwurf, $zusammenfassung, $public));
					} else {
						echo "<ul>";
						foreach($errors as $error){
							echo "<li>".$error."</li>";
						}
						echo "</ul>";
					}
				}
				break;
			}
			?>
		</div>
		<script>
			// Einstellungen des Datepickers
			$(".flatpickr").flatpickr({
				enableTime: true,
			    altInput: true,
			    altFormat: "j. F Y H:i",
			    dateFormat: "m-d-Y H:i",
			    /*minTime: "16:00",
    			maxTime: "22:00",*/
			    time_24hr: true
			});

			$('#standorte').val("default").prop("selected", true);

			$('#standorte').change(function () {
				var selectedOption = $(this).find("option:selected");
			    var selectedValue = selectedOption.val();
			    if(selectedValue == 'neuer-standort'){
			    	selectedOption.prop("selected", false);
			    	$.ajax({
						url: "standorte.php",
						type: 'POST',
						success : function(response) {    
							$('#standorteModal').append(response);
						}
					});
			    	UIkit.modal('#standorteModal').show();
			    }
			});
		</script>
	</body>
</html>	