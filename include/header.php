<!-- Zeichensatz -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- UIkit & Fontawesome Einbindung -->
<link rel="stylesheet" href="/css/uikit.min.css" />
<link rel="stylesheet" href="/css/fontawesome-all.css" />
<link rel="stylesheet" href="/css/custom.css" />
<script src="/js/uikit.min.js"></script>
<script src="/js/uikit-icons.min.js"></script>
<script src="/js/jquery-3.3.1.min.js"></script>

<!-- Schriftarten -->
<link href="https://fonts.googleapis.com/css?family=Indie+Flower" rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=EB+Garamond:400i" rel="stylesheet">

<!-- JavaScript Bibliotheken -->
<!-- Google Maps API -->
<script type="text/javascript" src='http://maps.google.com/maps/api/js?key=AIzaSyBorwcLkiG4pMmrgLLRlPltsxFSe4__1kU&libraries=places'></script>
<!-- locationpicker -->
<script src="/js/locationpicker.jquery.min.js"></script> 
<!-- flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/de.js"></script>
<!-- Deutsche Sprache für flatpickr anwenden -->
<script>
	flatpickr.localize(flatpickr.l10ns.de);
	flatpickr(".flatpickr");
</script>

<!-- Nützliche JavaScript Funktionen -->
<script>
	// Funktion zum Anzeigen der verbleibenden Zeichen in einem Eingabefeld
	function countChars(textbox, counter, max) {
		var count = max - document.getElementById(textbox).value.length;
		if (count == 0) { document.getElementById(counter).innerHTML = "<span style=\"color: red;\">" + count + "</span>"; }
		else { document.getElementById(counter).innerHTML = count; }
	}
</script>