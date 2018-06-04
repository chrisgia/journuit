<meta charset="utf-8">
<!-- UIkit & Fontawesome Einbindung -->
<link rel="stylesheet" href="/css/uikit.min.css" />
<link rel="stylesheet" href="/css/fontawesome-all.css" />
<link rel="stylesheet" href="/css/custom.css" />
<!-- Schriftarten -->
<link href="https://fonts.googleapis.com/css?family=Indie+Flower" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=EB+Garamond:400i" rel="stylesheet">
<!-- JavaScript Bibliotheken -->
<script src="/js/uikit.min.js"></script>
<script src="/js/uikit-icons.min.js"></script>
<script src="/js/jquery-3.3.1.min.js"></script>
<script>
	// Funktion zum Anzeigen der verbleibenden Zeichen in einem Eingabefeld
	function countChars(textbox, counter, max) {
		var count = max - document.getElementById(textbox).value.length;
		if (count < 0) { document.getElementById(counter).innerHTML = "<span style=\"color: red;\">" + count + "</span>"; }
		else { document.getElementById(counter).innerHTML = count; }
	}
</script>