<!-- Hinzufügen : if user logged out ... logged in... -->
<nav class="uk-navbar-container uk-height-1-1" id="navbar" uk-navbar>
	<div class="uk-navbar-left">
		<div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
                <li><a class="uk-link-heading" href="http://landausflugsplaner.de/pages/register.php"><span class="nav_link">REGISTRIEREN</span></a></li>
            </ul>
        </div>
        <div class="uk-navbar-item">
        	<img src="http://landausflugsplaner.de/pictures/dot.png" alt="dot" uk-img">
    	</div>
    	<div class="uk-navbar-item">
            <ul class="uk-navbar-nav">
        	   <li><a class="uk-link-heading" href="http://landausflugsplaner.de/pages/login.php"><span class="nav_link">ANMELDEN</span></a></li>
            </ul>
    	</div>
    </div>
    <div class="uk-navbar-right">
        <a class="uk-navbar-item uk-logo" href="http://landausflugsplaner.de"><span id="white">jour</span><span id="black">nuit</span> <img data-src="http://landausflugsplaner.de/pictures/journuit-logo_mini.png" alt="journuit Logo" uk-img></a>
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