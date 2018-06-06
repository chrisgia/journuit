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
		<title>journuit - Reisetagebücher</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<?php 
			switch ($view) {
				case 'neuer-eintrag':
				?>
				<div class="uk-margin-top uk-margin-bottom">
					<h1 class="uk-text-center">Neuer Eintrag</h1>
					<hr class="uk-width-1-1">

					<div id="titelbild" class="uk-margin uk-text-center">
			        	<!-- Hier erscheint das Titelbild sobald eins hochgeladen wird -->
			        </div>

					<form id="neuer-eintrag" method="POST">
					    <fieldset class="uk-fieldset">

					        <div class="uk-margin">
					        	<i><span id="char_count">25</span> verbleibend</i>
						        <input name="titel" id="titel" class="uk-input" type="text" placeholder="Titel (maximal 25 Zeichen)" onFocus="countChars('titel','char_count',25)" onKeyDown="countChars('titel','char_count',25)" onKeyUp="countChars('titel','char_count',25)" maxlength="25" required>
					        </div>

					        <div class="uk-margin">
						        <input name="beschreibung" class="uk-input" type="text" placeholder="Beschreibung..." required>
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
								        <!-- Dateigröße auf 5MB limitieren -->
								        <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
								        <span class="uk-link">direkter Auswahl</span>)
								    </div>
								</div>
								<progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
					        </div>

					        <input id="pictureId" name="pictureId" type="hidden" value="">
					        <input id="file_ext" name="file_ext" type="hidden" value="">

					    </fieldset>
					    <div class="uk-flex uk-flex-center uk-flex-middle">
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
	</body>
</html>	