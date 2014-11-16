<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block'>
				<h1 class='center'><span style='color: gray;'>my</span>|<span class='fxSlide'>cloud:upload</span></h1>
				<h2 class='center' style='font-size: 14px; color: gray;'>add files to your cloud</h2>
				<br />
				<hr />
				<br />
				<div class='center'>
					<a href='app.Upload' class='ajaxFriendly fxButton' style='font-weight: bold;'>upload</a> <a href='app.Cloud?style=prism' class='ajaxFriendly fxButton btnFor_prism'>anime</a> <a href='app.Cloud?style=text' class='ajaxFriendly fxButton btnFor_text'>text</a> <a href='app.Cloud?style=grid' class='ajaxFriendly fxButton btnFor_grid'>grid</a> <a href='app.PublicCloud' class='ajaxFriendly fxButton'>public files</a> <a href='app.Manager' class='ajaxFriendly fxButton'>manage</a>
				</div>
				<br />
				<br />
				<form action='core.ajaxUpload' enctype='multipart/form-data' id='ajaxUploader'>
					<input type='file' name='file[]' multiple onclick=\"$('#ajaxUploadProgress').fadeIn(200);\" />
					<input type='submit' value='(if it does not automatically upload)' class='fullSize' />
				</form>
				<br />
				<progress id='ajaxUploadProgress' style='width: 100%; display: none;'></progress>
				<br />
				<p id='ajaxUploadMessages'></p>
				<script>
					$(function() {
						$(':file').off();
						$(':file').on('change', function() {
							console.log('testing files...');
							var i = 0;
							var err = 0;
							for (; i < this.files.length; i++) {
								var file = this.files[i];
								var name = file.name;
								var size = file.size;
								var type = file.type;
								if (size > uploadMaxSize) {
									alert(\"The file: \" + name + \" is too large.\");
									err = 1;
								}
							}
							if (err == 0) {
								var formData = new FormData($('#ajaxUploader')[0]);
								console.log(formData);
								$.ajax({
									url: 'core.ajaxUpload',
									type: 'POST',
									xhr: function() {
										var myXhr = $.ajaxSettings.xhr();
										if (myXhr.upload) {
											myXhr.upload.addEventListener('progress', uploadUpdateProgressBar, false);
										}
										return myXhr;
									},
									beforeSend: uploadBeforeSendHandler,
									success: uploadSuccessHandler,
									error: uploadErrorHandler,
									data: formData,
									cache: false,
									contentType: false,
									processData: false
								});
							}
							else {
								alert(\"An error occurred, please try again.\");
							}
						});
					});
				</script>
			</div>
		</div>
	</div>
";
?>
