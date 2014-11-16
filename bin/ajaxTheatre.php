<?php
echo "
	<div id='content'>
		<div id='container'>
";
if (isset($_GET["getLibrary"])) {
	$prismDB = readDB($_sys["path_data"] . "db/.prism");
	foreach ($prismDB["anime"] as $id => $details) {
		$altNames = "";
		if (isSomething($details["english"])) {
			$altNames .= "English: {$details['english']} ";
		}
		if (isSomething($details["synonym"])) {
			$altNames .= "Synonyms: {$details['synonym']} ";
		}
		echo "
			<div style='background-image: url(core.DataGate?proxy={$details['cover']}); background-repeat: no-repeat; background-position: left center; background-size: 100px; padding-left: 120px; height: 150px;'>
				<h3>{$details['name']}</h3>
				<h2 class='noFlow'>{$altNames}</h2>
				<br />
		";
		if (isSomething($prismDB["files"][$id])) {
			$eps = array_reverse($prismDB["files"][$id], true);
			$latestEp = @array_pop(array_keys($eps));

			$latest = array_pop($eps);
			
			$name = htmlentities($details["name"]);
			echo "<span class='fxButton' onclick=\"preloadMedia('{$name}', '{$id}', '{$latestEp}')\">Episode {$latestEp} (Latest)</span> ";
			
			if (isSomething($eps)) {
				echo " <small>or</small> ";
				$rest = array_reverse($eps);
				echo "<select onchange=\"preloadMedia('{$name}', '{$id}', $(this).val());\">";
				echo "<option>select other episode...</option>";
				foreach ($eps as $num => $epdat) {
					echo "<option value='{$num}'>Episode {$num}</option>";
				}
				echo "</select>";
			}
		}
		echo "</div>";
	}
}

echo "
		</div>
	</div>
";
?>
