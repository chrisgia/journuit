<nav class="uk-navbar-container uk-height-1-1" id="navbar" uk-navbar>
	<div class="uk-navbar-left">
        <?php 
            if(isset($_SESSION['auth_logged_in']) && $_SESSION['auth_logged_in'] == true) {
                $id = $auth->getUserId();
                $selectUserData = $db->prepare("SELECT vorname, nachname, users.username FROM users_data JOIN users ON (users_data.id = users.id) WHERE users_data.id = ?");
                $selectUserData->execute(array($id));
                $userData = $selectUserData->fetchAll(\PDO::FETCH_ASSOC);
                $username = $userData[0]['username'];
        ?>
        <div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li>
                    <a class="uk-link-heading" href="/pages/profile.php">
                        <span class="nav_username">
                            <?php echo $username." (".$userData[0]['vorname']." ".$userData[0]['nachname'][0].".)";?>
                        </span>
                    </a>
                </li>
            </ul>
            <div uk-dropdown="animation: uk-animation-slide-top-small; duration: 500">
                <ul class="uk-nav uk-dropdown-nav">
                    <li><a href="/pages/logout.php" uk-icon="icon: sign-out">ABMELDEN  </a></li>
                </ul>
            </div>
        </div>
        <div class="uk-navbar-item">
            <img src="http://landausflugsplaner.de/pictures/dot.png" alt="dot" uk-img">
        </div>
        <div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li><a class="nav_icon" href="/pages/reisetagebuecher.php?view=meine-reisetagebuecher" uk-icon="icon: thumbnails; ratio: 1.5"></a></li>
            </ul>
        </div>
        <div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li><a class="nav_icon" href="/pages/reisetagebuecher.php?view=neues-reisetagebuch" uk-icon="icon: plus; ratio: 1.5"></a></li>
            </ul>
        </div>
        <?php } else {
        ?>
		<div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li><a class="uk-link-heading" href="/pages/register.php"><span class="nav_link">REGISTRIEREN</span></a></li>
            </ul>
        </div>
        <div class="uk-navbar-item">
        	<img src="http://landausflugsplaner.de/pictures/dot.png" alt="dot" uk-img">
    	</div>
    	<div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
        	   <li><a class="uk-link-heading" href="/pages/login.php"><span class="nav_link">ANMELDEN</span></a></li>
            </ul>
    	</div>
         <?php } ?>
    </div>
    <div class="uk-navbar-right">
        <a class="uk-navbar-item uk-logo" href="/"><span id="white">jour</span><span id="black">nuit</span> <img data-src="/pictures/journuit-logo_mini.png" alt="journuit Logo" uk-img></a>
    </div>
</nav>