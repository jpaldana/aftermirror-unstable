var uploadMaxSize = 1024 * 1024 * 100; // 100 MB

function uploadUpdateProgressBar(e) {
	if (e.lengthComputable) {
		$('#ajaxUploadProgress').attr({ value: e.loaded, max: e.total });
	}
}

function uploadBeforeSendHandler(e) {
	console.log('uploading file(s)...');
	//console.log(e);
}

function uploadSuccessHandler(e) {
	console.log('successfully uploaded file(s)...');
	$('#ajaxUploadMessages').prepend(e);
}

function uploadErrorHandler(e) {
	console.log('failed to upload file(s)...');
	//console.log(e);
}