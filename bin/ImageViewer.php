<?php
echo "
	<div id='content'>
		<div id='container'>
			<div class='block' style='min-height: 320px; min-width: 480px;'>
";
if (isset($_GET["image"])) {
	$ak = readDB($_sys["path_data"] . "db/.access-key");
	$data = $ak[$_GET["image"]];
	if (fextIsImage($data["local"])) {
		echo "
					<div style='width: 100%;'>
						<img src= 'core.DataGate?direct={$_GET['image']}' id='imageBase' style='width: 100%;' class='panzoom' />
					</div>
					<span class='buttonLink fxBackground' onclick=\"miniWindow('app.ajaxImageOptions?image={$_GET['image']}', 'imageOptionPopupDialog');\">Options...</span>
					<script>
						$(function() {
							var \$panzoom = \$('.panzoom').panzoom();
								\$panzoom.parent().on('mousewheel.focal', function(e) {
									e.preventDefault();
									var delta = e.delta || e.originalEvent.wheelDelta;
									var zoomOut = delta ? delta < 0 : e.originalEvent.deltaY > 0;
									\$panzoom.panzoom('zoom', zoomOut, {
										increment: 0.1,
										animate: false,
										focal: e
									});
								});
							});
					</script>
		";
	}
}
echo "
			</div>
		</div>
	</div>
";
?>