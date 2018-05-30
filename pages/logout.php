<?php require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; ?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php";  
		?>
		<title>journuit - Abmeldung</title>
	</head>

	<body class="uk-height-viewport">
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">

				<div class="uk-margin-xlarge-top">
					<span id="journuit_big">
						<span id="white">jour</span><span id="black">nuit</span> <img data-src="/pictures/journuit-logo_big.png" alt="journuit Logo" uk-img>
					</span>
				</div>

				<div class="uk-margin-medium-top">
					<?php $auth->logOut(); ?>

					<div class="uk-alert-primary" uk-alert>
						<p>Sie werden in kürze umgeleitet...</p>
					</div>	

				</div>
			</div>
		</div>

		<script>
			UIkit.notification({message: 'Sie wurden erfolgreich abgemeldet.', status: 'success'}); 
			setInterval(function(){ window.location.replace('/'); }, 3000);
		</script>

	</body>
</html>