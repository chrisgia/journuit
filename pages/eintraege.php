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

				?>
				<div class="uk-margin-top uk-margin-bottom">
					<h1 class="uk-text-center">Neuer Eintrag</h1>
					<hr class="uk-width-1-1">

					<form id="neuer-eintrag" method="POST">
					    <fieldset class="uk-fieldset">

					    	<div class="uk-margin">
						    	<label>Zusammenfassung <input name="zusammenfassung" class="uk-checkbox" type="checkbox" value="1"></label>
					        </div>

					        <div class="uk-margin">
					        	<!-- Selectbox mit den gespeicherten Standorten des Benutzers -->
					        	<select id="standorte" class="uk-select uk-form-width-medium" name="standorte">
					        		<option value="default">Standort auswählen</option>
					        		<option value="neuer-standort" class="uk-text-bold">Neuer Standort</option>
					        		<?php 
					        		foreach($standorte as $standort){
					        			echo "<option value=\"".$standort['id']."\">".$standort['name']."</option>";
					        		}
					        		?>
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
						        <textarea name="beschreibung" class="uk-textarea" rows="5" placeholder="Eintrag..." required></textarea>
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
						if(!insertBild($db, $username, $_POST['pictureId'], $_POST['file_ext'])) {
							array_push($errors, 'Das Bild konnte nicht eingefügt werden.');
						}
					}

					if(empty($errors)){
						$userId = $auth->getUserId();
						$url = uniqueDbId($db, 'reisetagebuecher', 'url');
						$insertReisetagebuch = $db->prepare("INSERT INTO reisetagebuecher(users_id, titel, beschreibung, url, public, bild_id) VALUES(?, ?, ?, ?, ?, ?)");
						$insertReisetagebuch->execute(array(htmlspecialchars($userId), htmlspecialchars($_POST['titel']), htmlspecialchars($_POST['beschreibung']), $url, $public, htmlspecialchars($_POST['pictureId'])));
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

			 $('#standorte').change(function () {
			    var selectedOption = $(this).find("option:selected");
			    var selectedValue  = selectedOption.val();
			    if(selectedValue == 'neuer-standort'){
			    	selectedOption.prop("selected", false);
			    	window.location = "standorte.php?view=neuer-standort";
			    }
			 });
		</script>
	</body>
</html>	