<?php 
	session_start(); 

	if(isset($_GET["view"])) {
		$view = htmlspecialchars($_GET["view"]);
	} elseif(isset($_POST["view"])) {
		$view = htmlspecialchars($_POST["view"]);
	} else {
		$view = "meine-reisetagebuecher";
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
			require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
		?>
		<title>journuit - Reisetagebücher</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
				<?php 
				switch ($view) {
					case 'meine-reisetagebuecher':	
				?>
				<?php 
				break;

				case 'neues-reisetagebuch':
				?>
				<div class="uk-margin-top uk-margin-bottom">
					<h1 class="uk-text-center">Neues Reisetagebuch</h1>
					<hr class="uk-width-1-1">
					<form method="POST">
					    <fieldset class="uk-fieldset">

					        <div class="uk-margin">
						        <input name="titel" class="uk-input" type="text" placeholder="Titel...">
					        </div>

					        <div class="uk-margin">
						        <input name="beschreibung" class="uk-input" type="text" placeholder="Beschreibung...">
					        </div>

					        <div class="uk-margin">
						    	<label>Öffentlich <input name="public" class="uk-checkbox" type="checkbox"></label>
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

					    </fieldset>
					    <div class="uk-flex uk-flex-center uk-flex-middle">
					    	<button class="uk-button uk-button-default" name="save" value="true">Speichern</button>
					    </div>
					</form>
					<hr class="uk-width-1-1">
				</div>
				<?php 
				break;
				}
			?>
			</div>
		</div>
		<?php 
			if(isset($_GET['login'])){echo "<script>UIkit.notification({message: 'Sie sind angemeldet.', status: 'success'});</script>";}
		?>
		<script>
			// Skript zum uploaden vom Titelbild
			var bar = document.getElementById('js-progressbar');
		    UIkit.upload('.js-upload', {

		        url: '/include/upload.php',
		        multiple: false,
		        mime: 'image/*',
		        method: 'POST',

		        beforeSend: function () {
		            console.log('beforeSend', arguments);
		        },
		        beforeAll: function () {
		            console.log('beforeAll', arguments);
		        },
		        load: function () {
		            console.log('load', arguments);
		        },
		        error: function () {
		            console.log('error', arguments);
		        },
		        complete: function () {
		            console.log('complete', arguments);
		        },

		        loadStart: function (e) {
		            console.log('loadStart', arguments);

		            bar.removeAttribute('hidden');
		            bar.max = e.total;
		            bar.value = e.loaded;
		        },

		        progress: function (e) {
		            console.log('progress', arguments);

		            bar.max = e.total;
		            bar.value = e.loaded;
		        },

		        loadEnd: function (e) {
		            console.log('loadEnd', arguments);

		            bar.max = e.total;
		            bar.value = e.loaded;
		        },

		        completeAll: function () {
		            console.log('completeAll', arguments);

		            setTimeout(function () {
		                bar.setAttribute('hidden', 'hidden');
		            }, 1000);

		            UIkit.notification({message: 'Ihr Titelbild wurde erfolgreich hochgeladen.', status: 'success'});
		        }
		    });
		</script>
	</body>
</html>