<?php session_start(); ?>
<!DOCTYPE html>
<html>
	<head>
		<?php require "include/header.php"; ?>
		<title>journuit - Startseite</title>
	</head>

	<body class="uk-height-viewport">
		<?php require "include/navbar.php"; ?>
		<div id="banner" class="uk-height-large uk-flex uk-flex-center uk-flex-middle uk-background-cover" data-src="pictures/sunset-1920w.png" data-srcset="pictures/sunset-375w.png 375w, pictures/sunset-1920w.png 1920w" uk-img>
			<h1 id="slogan" class="uk-text-center uk-margin-large-top">Verewige deine Errinerungen. <br/><span id="white">Tag</span> und Nacht.</h1>
			<!-- <h1 id="slogan" class="uk-text-top uk-margin-large-top uk-hidden@m">Verewige deine Errinerungen. <br/><span id="white">Tag</span> und Nacht.</h1> -->
		</div>

		<div class="uk-container uk-container-large">
			<div uk-grid class="uk-flex-center uk-flex-middle">
				<div uk-grid class="uk-flex-center uk-flex-middle">
					<div class="uk-width-1-4 uk-flex-first">
						<img data-src="pictures/devices_mockup.png" alt="devices" uk-img>
					</div>
					<div class="uk-width-1-2 uk-flex-last">
						<span id="beschreibung">Mit journuit können Sie jederzeit und von jedem Gerät aus Ihre schönsten Momente festhalten.</span>
					</div>
				</div>
				<hr class="uk-width-1-1 uk-divider-icon">
				<div uk-grid class="uk-flex-center uk-flex-middle">
					<div class="uk-width-1-2 uk-flex-first">
						<a href="http://www.euresa-reisen.de" target="_blank"><img data-src="pictures/logo-er.png" alt="EURESA Logo" uk-img></a>
					</div>
					<div class="uk-width-1-2 uk-flex-last">
						<a href="https://www.dfhi-isfates.eu/de/" target="_blank"><img data-src="pictures/logo-dfhi_isfates.png" alt="DFHI/ISFATES Logo" uk-img></a>
					</div>
				</div>
			</div>
		</div>
		<!-- Success Notification wenn der Benutzer gerade ein Konto erstellt, oder sich eingeloggt hat -->
		<?php
			if(isset($_GET['register'])){echo "<script>UIkit.notification({message: 'Ihr Konto wurde erfolgreich erstellt !', status: 'success'});</script>";}
			if(isset($_GET['login'])){echo "<script>UIkit.notification({message: 'Sie sind angemeldet.', status: 'success'});</script>";}
		?>
	</body>
</html>