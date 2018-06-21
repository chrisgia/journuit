<?php 
	require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php';
?>
<!DOCTYPE html>
<html>
	<head>
		<?php 
			require $_SERVER['DOCUMENT_ROOT']."/include/header.php"; 
		?>
		<title>journuit - Registrierung</title>
	</head>

	<body class="uk-height-viewport">
		<?php require $_SERVER['DOCUMENT_ROOT']."/include/navbar.php";?>
		<div class="uk-container uk-container-large">
			<div class="uk-flex uk-flex-column uk-flex-center uk-flex-middle">
				<div class="uk-margin-top">
					<span id="journuit_big">
						<span class="white">jour</span><span class="black">nuit</span> <img data-src="http://landausflugsplaner.de/pictures/journuit-logo_big.png" alt="journuit Logo" uk-img>
					</span>
				</div>
				<div class="uk-margin-top uk-margin-bottom">
					<form method="POST">
					    <fieldset class="uk-fieldset">

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: info"></span>
						            <input name="vorname" class="uk-input" type="text" placeholder="Vorname..." value="<?php if(isset($_POST['vorname'])){echo $_POST['vorname'];}?>">
						        </div>
					        </div>

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: info"></span>
						            <input name="nachname" class="uk-input" type="text" placeholder="Nachname..." value="<?php if(isset($_POST['nachname'])){echo $_POST['nachname'];}?>">
						        </div>
					        </div>

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: mail"></span>
						            <input name="email" class="uk-input" type="text" placeholder="Email-Adresse..." value="<?php if(isset($_POST['email'])){echo $_POST['email'];}?>">
						        </div>
					        </div>

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: user"></span>
						            <input name="username" class="uk-input" type="text" placeholder="Benutzername..." value="<?php if(isset($_POST['username'])){echo $_POST['username'];}?>">
						        </div>
					        </div>

					        <div class="uk-margin">
					            <div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: lock"></span>
						            <input name="passwort" class="uk-input" type="password" placeholder="Passwort...">
						        </div>
					        </div>

					        <div class="uk-margin">
					        	<div class="uk-inline">
						            <span class="uk-form-icon" uk-icon="icon: lock"></span>
						            <input name="passwort_confirm" class="uk-input" type="password" placeholder="Passwort wiederholen...">
						        </div>
					        </div>
					    </fieldset>
					    <div class="uk-flex uk-flex-center uk-flex-middle">
					    	<button class="uk-button uk-button-default" name="register">Registrieren</button>
					    </div>
					</form>
				</div>
				<?php
					if(isset($_POST['register'])){
						try {

							$error = "";

							$vorname = ucfirst(str_replace(" ", "", trim($_POST['vorname'])));
							$nachname = ucfirst(str_replace(" ", "", trim($_POST['nachname'])));
							$username = trim($_POST['username']);

							if(empty($vorname)){
								$error = "Der Vorname darf nicht leer sein.";
							} 
							if(empty($nachname)){
								$error = "Der Nachname darf nicht leer sein.";
							}

							if(empty($nachname)){
								$error = "Der Nachname darf nicht leer sein.";
							}

							$authorizedChars = array('-', '_'); 

							if(!ctype_alnum(str_replace($authorizedChars, '', $username))) { 
							    $error = "Der Benutzername darf nur alphanumerische Zeichen sowie '-' und '_' enthalten."; 
							} 

							if($_POST['passwort'] != $_POST['passwort_confirm']){
								$error = "Die Passwörter stimmen nicht überein.";
							}

							if(empty($error)){
								$userId = $auth->registerWithUniqueUsername(htmlspecialchars($_POST['email']), htmlspecialchars($_POST['passwort']), htmlspecialchars($username));
							}
						}
						catch (\Delight\Auth\InvalidEmailException $e) {
						   $error = "Die Email-Adresse ist ungültig.";
						}
						catch (\Delight\Auth\InvalidPasswordException $e) {
						    $error = "Das Passwort ist ungültig.";
						}
						catch (\Delight\Auth\UserAlreadyExistsException $e) {
						    $error = "Benutzer existiert bereits.";
						}
						catch (\Delight\Auth\TooManyRequestsException $e) {
						    $error = "Die maximale Anzahl an Anfragen wurde überschritten.";
						}
						catch (\Delight\Auth\DuplicateUsernameException $e) {
						    $error = "Dieser Benutzername ist bereits vorhanden.";
						}

						if(!empty($error)){
							echo "<div class=\"uk-alert-danger\" uk-alert>";
    						echo 	"<a class=\"uk-alert-close\" uk-close></a>";
    						echo 	"<p>".$error."</p>";
							echo "</div>";
						} else {
							$insertUserData = $db->prepare("INSERT INTO users_data(vorname, nachname) VALUES(?, ?)");
							$insertUserData->execute(array(htmlspecialchars($vorname), htmlspecialchars($nachname)));
							$root = filter_input(INPUT_SERVER, 'DOCUMENT_ROOT');
							mkdir($root."/users/$username/", 0755, true);
							echo "<script>window.location.replace('login.php?register=success');</script>";
						}
					}
				?>
			</div>
		</div>
	</body>
</html>