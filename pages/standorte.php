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
		<title>journuit - Standorte</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
			<?php 
			switch ($view) {
				case 'neuer-standort':
				?>
				<div class="uk-margin-top uk-margin-bottom">
					<h1 class="uk-text-center">Neuer Standort</h1>
					<hr class="uk-width-1-1">

					<form id="neuer-standort" method="POST">
					    <fieldset class="uk-fieldset">

					        <div class="uk-margin">
					        	<!-- Standorteingabe -->
					        	<label class="uk-form-label">Standorteingabe</label>
					        	<div class="uk-form-controls">
						        	<div class="uk-inline">
										<span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: location"></span>
						        		<input type="text" class="uk-input uk-form-width-large" id="locationInput" placeholder="Standort"/>
						        	</div>
					        	</div>
					        </div>

					        <div>
					        	<span class="uk-text-small uk-text-lead">Latitude</span>
					        	<span class="uk-text-small uk-text-lead uk-float-right">Longitude</span>
					        </div>

					        <div class="uk-margin-bottom">
					        	<input type="text" class="uk-input uk-form-width-small" id="lat" name="lat" placeholder="Latitude"/>
					        	<input type="text" class="uk-input uk-form-width-small uk-float-right" id="lon" name="lon" placeholder="Longitude"/>
					        </div>

					        <!-- Standort von einem Bild erkennen -->
 							<div class="uk-margin">
						    	<div id="standortVonBild" class="uk-placeholder uk-text-center">
						    		<span uk-icon="icon: location"></span>
								    <span class="uk-text-middle">Standort per Bild (via Drag & Drop oder </span>
								    <div uk-form-custom>
								        <input type="file" name="files">
								        <!-- Dateigröße auf 5MB limitieren -->
								        <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
								        <span class="uk-link">direkter Auswahl</span>)
								    </div>
								</div>
								<progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
					        </div>

					        <div id="pickerMap" style="width: 500px; height: 400px;"></div>

					        <div class="uk-margin">
					        	<div class="uk-inline">
									<span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: quote-right"></span>
					        		<input type="text" class="uk-input uk-form-width-large" id="name" placeholder="Standortname..."/>
					        	</div>
					        </div>

					        <!-- Bild für den Standort anlegen -->
					        <div class="uk-margin">
						    	<div class="js-upload uk-placeholder uk-text-center">
						    		<span uk-icon="icon: cloud-upload"></span>
								    <span class="uk-text-middle">Standortbild (via Drag & Drop oder </span>
								    <div uk-form-custom>
								        <input type="file" name="files">
								        <!-- Dateigröße auf 5MB limitieren -->
								        <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
								        <span class="uk-link">direkter Auswahl</span>)
								    </div>
								</div>
								<progress id="js-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
					        </div>
					    </fieldset>
					    <div class="uk-flex uk-flex-center uk-flex-middle">
					    	<button class="uk-button uk-button-default" name="create">Erstellen</button>
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
		
		<script>
			// Einstellungen des Locationpickers
			$('#pickerMap').locationpicker({
		        location: {
		            latitude: 49.21202227196742,
		            longitude: 6.856657780487012
		        },
		        inputBinding: {
		            latitudeInput: $('#lat'),
		            longitudeInput: $('#lon'),
		            locationNameInput: $('#locationInput')
		        },
		        addressFormat: 'street_address',
		        enableAutocomplete: true,
		        radius: null
		    });

		    var bar = document.getElementById('js-progressbar');
			var username = "<?php echo $username; ?>";

			// Skript zum uploaden von Bildern
		    UIkit.upload('#standortVonBild', {

		        url: '/include/standortVonUpload.php',
		        multiple: false,
		        mime: 'image/*',
		        method: 'POST',

		        beforeSend: function () {
		        },
		        beforeAll: function () {
		        },
		        load: function () {
		        },
		        error: function () {
		        	console.log('test');
		        },
		        complete: function () {
		        },

		        loadStart: function (e) {
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
		            setTimeout(function () {
		                bar.setAttribute('hidden', 'hidden');
		            }, 1000);

		            var exifData = JSON.parse(data.response);
		            // Machen dass der Input geändert wird
		            $('#lat').val(exifData.lat);
		            $('#lon').val(exifData.lon);
		            $('#pickerMap').locationpicker({
				        inputBinding: {
				            latitudeInput: $('#lat'),
				            longitudeInput: $('#lon')
				        },
				        radius: null
		        	});
		        }
		    });
		</script>
	</body>
</html>