<?php
echo "
		<div id='content'>
			<div id='container'>
				<div id='poster' class='block'>
					<form id='statusPosterPlaceholder'>
						<input type='text' onfocus='showPoster();' onclick='showPoster();' placeholder='post something...' />
					</form>
					<form id='statusPoster' style='display: none;' action='core.StatusPoster' method='post'>
						<label for='post_title' class='textboxLabel'>Title</label>
						<input type='text' name='post_title' id='post_title' style='font-size: 18px;' />
						<label for='post_content' class='textboxLabel'>Content</label>
						<textarea name='post_content' id='post_content' cols='1' rows='1' style='height: 34px;'></textarea>
						<br />
						<span style='margin-left: 2em;'>Visible to...</span>
						<br />
						<input type='radio' name='visibility' id='visible_friends' value='friends' checked><label for='visible_friends'> Friends</label>
						<br />
						<input type='radio' name='visibility' id='visible_public' value='public'><label for='visible_public'> Public</label>
";
$level = readDB(myDat());
if ($level["access"] == 9) {
	echo "
		<br />
		<input type='radio' name='visibility' id='visible_admin' value='admin'><label for='visible_admin'> Notice (Post as Admin)</label>
	";
}
echo "
						<br />
						<br />
						<div class='hAlignContainer'>
							<div class='hAlign'>
								<input type='submit' value='Post' /><input type='reset' value='Reset' />
							</div>
						</div>
					</form>
				</div>
				<div id='newsfeed'></div>
				<script>
					function showPoster() {
						$('#statusPosterPlaceholder').fadeOut(200);
						$('#statusPoster').fadeIn(200);
						$('textarea').autogrow().focus();
					}
					$(function() {
						$('#newsfeed').load('core.ajaxNewsfeed?current=0');
					});
				</script>
			</div>
		</div>
";
?>
