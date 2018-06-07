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
					        	<!-- Selectbox mit Standorten -->
					        	<div class="uk-inline">
									<span class="uk-form-icon uk-form-icon-flip" uk-icon="icon: location"></span>
					        		<input type="text" class="uk-input uk-form-width-large" id="us3-address" placeholder="Standort"/>
					        	</div>
					        </div>

					        <!-- Versteckte Google Map damit der Location picker funktioniert -->
					        <div id="us3" style="width: 500px; height: 400px;" hidden></div>
					    </fieldset>
					</form>
				</div>
			<?php
			break;
			}
			?>
		</div>
		
		<script>
			// Einstellungen des Locationpickers
			$('#us3').locationpicker({
			    location: {
			       	latitude: null,
					longitude: null
			    },
			    inputBinding: {
			        locationNameInput: $('#us3-address')
			    },
			    enableAutocomplete: true,
			    addressFormat: 'locality',
			    onchanged: function (currentLocation, radius, isMarkerDropped) {
			        console.log("Location changed. New location (" + currentLocation.latitude + ", " + currentLocation.longitude + ")");
			    }
			});
		</script>
	</body>
</html>