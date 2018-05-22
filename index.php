<!DOCTYPE html>
<html>
	<head>
		<title>Journuit - Startseite</title>
		<meta charset="utf-8">
        <!-- UIkit & Fontawesome Einbindung -->
        <link rel="stylesheet" href="css/uikit.min.css" />
        <link rel="stylesheet" href="css/fontawesome-all.css" />
        <link rel="stylesheet" href="css/custom.css" />
        <script src="js/uikit.min.js"></script>
        <script src="js/uikit-icons.min.js"></script>
        <!-- Schriftarten -->
        <link href="https://fonts.googleapis.com/css?family=Indie+Flower" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=EB+Garamond:400i" rel="stylesheet">
	</head>

	<body>
		<nav class="uk-navbar-container uk-height-1-1" id="navbar" uk-navbar>
			<div class="uk-navbar-left">
				<div class="uk-navbar-item">
		        	<a class="uk-link-heading" href="index.php/register.php">REGISTRIEREN</a>
		        </div>
		        <div class="uk-navbar-item">
		        	<img src="pictures/dot.png" alt="dot" uk-img">
		    	</div>
		    	<div class="uk-navbar-item">
		        	<a class="uk-link-heading" href="index.php/login.php">ANMELDEN</a>
		    	</div>
		    </div>
		    <div class="uk-navbar-right">
		        <a class="uk-navbar-item uk-logo" href="index.php"><span id="white">jour</span><span id="black">nuit</span> <img data-src="pictures/journuit-logo_mini.png" alt="journuit Logo" uk-img></a>
		    </div>
		</nav>

		<div id="banner" class="uk-height-large uk-flex uk-flex-center uk-flex-middle uk-background-cover" data-src="pictures/sunset-1920w.png" data-srcset="pictures/sunset-375w.png 375w, pictures/sunset-1920w.png 1920w" uk-img>
			<h1 id="slogan" class="uk-text-center uk-margin-large-top">Verewige deine Errinerungen. <br/><span id="white">Tag</span> und Nacht.</h1>
			<h1 id="slogan" class="uk-text-top uk-margin-large-top uk-visible@s">Verewige deine Errinerungen. <br/><span id="white">Tag</span> und Nacht.</h1>
		</div>

		<div class="uk-container uk-container-expand uk-height-match">
			<div uk-grid class="uk-flex-center uk-flex-middle">
				<div class="uk-width-1-4">
					<img data-src="pictures/devices_mockup.png" alt="devices" uk-img>
				</div>
				<div class="uk-width-1-2">
					<span id="beschreibung">Mit journuit können Sie jederzeit und von jedem Gerät aus Ihre schönsten Momente festhalten.</span>
				</div>
			</div>
		</div>

	</body>
</html>