var bar1 = document.getElementById('js-progressbar');

// Skript zum ermitteln des Standorts von einem Bild
UIkit.upload('#standortVonBild', {

	url: '/include/standortVonUpload.php',
	multiple: false,
	mime: 'image/*',
	method: 'POST',

	beforeSend: function () {
	},
	beforeAll: function () {
	},
	load: function () {
	},
	error: function () {
		console.log('test');
	},
	complete: function () {
	},

	loadStart: function (e) {
		bar1.removeAttribute('hidden');
		bar1.max = e.total;
		bar1.value = e.loaded;
	},

	progress: function (e) {
		bar1.max = e.total;
		bar1.value = e.loaded;
	},

	loadEnd: function (e) {
		bar1.max = e.total;
		bar1.value = e.loaded;
	},

	completeAll: function (data) {
		bar1.setAttribute('hidden', 'hidden');

		var exifData = JSON.parse(data.response);
		if('error' in exifData){
			UIkit.notification({message: exifData.error, status: 'danger'});
		} else {
			$('#lat').val(exifData.lat);
			$('#lon').val(exifData.lon);

			$('#pickerMap').locationpicker({
				inputBinding: {
					latitudeInput: $('#lat'),
					longitudeInput: $('#lon')
				},
				radius: null
			});
		}				   
	}
});

var bar2 = document.getElementById('js-progressbar2');

// Skript zum uploaden vom Standortbild
UIkit.upload('#standortBildUpload', {

	url: '/include/upload.php',
	multiple: false,
	mime: 'image/*',
	method: 'POST',
	params: {
		width: 300,
		height: 300
	},

	beforeSend: function () {
	},
	beforeAll: function () {
	},
	load: function () {
	},
	error: function () {
	},
	complete: function () {
	},

	loadStart: function (e) {
		$('#loading1').removeAttr('hidden');
		bar2.removeAttribute('hidden');
		bar2.max = e.total;
		bar2.value = e.loaded;
	},

	progress: function (e) {
		bar2.max = e.total;
		bar2.value = e.loaded;
	},

	loadEnd: function (e) {
		bar2.max = e.total;
		bar2.value = e.loaded;
	},

	completeAll: function (data) {
		bar2.setAttribute('hidden', 'hidden');

		var infos = JSON.parse(data.response);
		var fullPath = '../users/'+username+'/tmp_'+infos.pictureId+'.'+infos.file_ext;

		$('#pictureId').val(infos.pictureId);
		$('#file_ext').val(infos.file_ext);
		$('#standortBild').empty().append('<div class="uk-animation-fade"><img class="uk-border-rounded" data-src="'+fullPath+'" uk-img></div>');
		UIkit.notification({message: 'Ihr Standortbild wurde erfolgreich hochgeladen.', status: 'success'});
		$('#loading1').attr('hidden', 'hidden');
	}
});