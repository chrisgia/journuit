<?php
    session_start();
    require $_SERVER['DOCUMENT_ROOT'].'/include/db_connect.php'; 
?>
<nav class="uk-navbar-container uk-height-1-1" id="navbar" uk-navbar>
	<div class="uk-navbar-left">
        <?php 
            if(isset($_SESSION['auth_logged_in']) && $_SESSION['auth_logged_in'] == true) {
                $id = $auth->getUserId();
                $selectUserData = $db->prepare("SELECT * FROM users_data WHERE id = ?");
                $selectUserData->execute(array($id));
                $userData = $selectUserData->fetchAll(\PDO::FETCH_ASSOC);
        ?>
        <div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li>
                    <a class="uk-link-heading" href="/pages/profile.php">
                        <span class="nav_username">
                            <?php echo $userData[0]['vorname']." ".$userData[0]['nachname'][0].". (#".str_pad($id, 4, '0', STR_PAD_LEFT).")";?>
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
                <li><a class="nav_icon" href="/pages/reisetagebuecher.php" uk-icon="icon: thumbnails; ratio: 1.5"></a></li>
            </ul>
        </div>
        <div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li><a class="nav_icon" href="/pages/reisetagebuecher.php" uk-icon="icon: plus; ratio: 1.5"></a></li>
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

<script>
    // Warum funktionniert das nicht ?
    $(document).ready(function() {
        console.log(location.pathname);
        console.log("test");
        $('a[href="http://landausflugsplaner.de' + location.pathname + '"]').addClass('active');
    });
</script>