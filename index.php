<?php
/*
 * author: 	mpriess
 * date:	2013-08-26
 * purpose:	recolores and resizes the selected icons and return the url to the download-zip
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>SvgRecolorResize</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Gives an overview over all existing Icons">
		<meta name="author" content="mpriess">

		<script src="./bootstrap/js/jquery-2.0.3.min.js"></script>
		<script src="./bootstrap/js/bootstrap.min.js"></script>
		<script src="./bootstrap/js/bootbox.min.js"></script>
		<script src="./jquery-minicolors/jquery.minicolors.min.js"></script>

		<script type="text/javascript">
			var toDownloadIcons = {
				"color" : $('#recolor').data('color'),
				"sizes" : [],
				"data" : []
			};
			$(document).ready(function() {
				$("div.iconSelect").each(function() {
					$(this).click(function() {
						$(this).toggleClass("selected");
					});
				});
				$("#selectAll").click(function() {
					$("div.iconSelect").each(function() {
						$(this).addClass("selected");
					});
				});
				$("#clearSelection").click(function() {
					$("div.iconSelect.selected").each(function() {
						$(this).removeClass("selected");
					});
				});
				$('#recolor').minicolors({
							defaultValue : $('#recolor').data('color'),
							position : 'bottom right',
							change : function(hex, opacity) {
								$('#recolor').data('color', hex);
							}
						});
				$("#downloadIcons").click(function() {
					toDownloadIcons = {
						"color" : $('#recolor').data('color'),
						"sizes" : [],
						"data" : []
					};
					var count = 0;
					$("div.iconSelect.selected").each(function() {
						toDownloadIcons.data.push({
							'dir' : $(this).data("dir"),
							'name' : $(this).data("name")
						});
						count++;
					});
					if (count > 0) {
						$("#modalCount").html("<span>" + count + "</span>" + (count > 1 ? " icons" : " icon"));
						$('#modalDownload').modal();
					} else {
						bootbox.alert("Pleaes select some icons before!");
					}
				});
				$("#genZip").click(function() {

					$('#downloadForm input[type=checkbox]').each(function() {
						if ($(this).is(":checked")) {
							toDownloadIcons.sizes.push(this.value);
						}
					});
					toDownloadIcons.color = $('#recolor').data('color');
					$("#pleaseWaitDialog").modal();
					$('#modalDownload').modal("hide");

					$.post("genZip.php", {
						data : JSON.stringify(toDownloadIcons)
					}, function(retData) {
						$("body").append("<iframe src='" + retData.url + "' style='display: none;' ></iframe>");
						$("#pleaseWaitDialog").modal("hide");
					}, "json");
				});
				$('div.iconSelect').tooltip({'placement' : 'bottom'});
			});
		</script>

		<!-- Le styles -->
		<link href="./bootstrap/css/bootstrap.css" rel="stylesheet">
		<style type="text/css">
			body {
				padding-top: 20px;
				padding-bottom: 60px;
				
				
				-webkit-touch-callout: none;
				-webkit-user-select: none;
				-khtml-user-select: none;
				-moz-user-select: -moz-none;
				-ms-user-select: none;
				user-select: none;
			}

			/* Custom container */
			.container > hr {
				margin: 60px 0;
			}

			.container, div.navbar-inner-content {
				max-width: 820px;
				padding-left: 10px;
				margin: 0 auto;
			}

			.navbar {
				margin-bottom: 20px;
			}

			div.row {
				margin-left: 0;
			}

			div.iconSelect {
				padding: 3px;
				border: 1px solid #ccc;
				-moz-border-radius: 6px;
				border-radius: 6px;
				text-align: center;
				margin: 6px;
				width: 59px;
				height: 59px;
				cursor: pointer;
			}
			div.iconSelect.selected {
				background-color: #eee;
				border-color: #808080;
			}
			#modalCount span {
				color: #f44343;
			}
			#modalDownload .bs-docs-grid {
				margin-bottom: 45px;
			}
			.footer {
				margin-top: 20px;
			}
			
			.tooltip.in {
				opacity: 1.0 !important;
			}
		</style>
		<link href="./bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="./jquery-minicolors/jquery.minicolors.css" rel="stylesheet">

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="./bootstrap/js/html5shiv.js"></script>
		<![endif]-->

		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="./bootstrap/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="./bootstrap/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="./bootstrap/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="./bootstrap/ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="./bootstrap/ico/favicon.png">
	</head>

	<body>
		<div class="container">
			<div class="navbar navbar-fixed-top">
				<div class="navbar-inner">
					<div class="navbar-inner-content">
						<a class="brand" href="#">SvgRecolorResize</a>
						<button class="btn btn-inverse" id="selectAll">
							Select all
						</button>
						<button class="btn btn-danger" id="clearSelection">
							Clear selection
						</button>
						<button class="btn btn-info" id="downloadIcons">
							Download
						</button>
					</div>
				</div>
			</div>

			<br />

			<?php
			
			function dirToArray($dir) {
				$result = array();
				$cdir = scandir($dir);

				foreach ($cdir as $key => $value) {
					if (!in_array($value, array(".", ".."))) {
						if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
							$result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
						} else {
							$result[] = $value;
						}
					}
				}

				return $result;
			}

			$files = dirToArray("./icons/");

			foreach ($files as $dir => $content) {
				if (preg_match("/^[0-9a-z]/i", $dir) && count($content) > 1) {
					echo "<h2>" . $dir . "</h2>\n";
					echo "<div class=\"bs-docs-grid\">\n";
					$counter = 0;
					foreach ($content as $icon) {
						if (preg_match("/.svg$/i", $icon)) {
							if (($counter % 10) == 0) {
								echo "<div class=\"row show-grid\">\n";
							}

							echo "<div class=\"span1 iconSelect\" data-original-title=\"" . $icon . "\" title=\"" . str_replace(".svg", "", $icon) . "\" data-dir=\"" . $dir . "\" data-name=\"" . $icon . "\"><img src=\"./icons/" . $dir . "/" . $icon . "\" width=\"56\" height=\"56\"></div>\n";

							if (($counter % 10) == 9) {
								echo "</div>\n";
							}
							$counter++;
						}
					}
					if (($counter % 10) != 0) {
						echo "</div>\n";
					}
					echo "</div>\n";
				}
			}
			?>
			<div class="footer">
			<p> &copy; by <a href="http://mpriess.de">mpriess.de</a> </p>
			</div>

			</div>


			<div id="modalDownload" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					×
				</button>
				<h3>Download <span id="modalCount"></span></h3>
			</div>
			<div class="modal-body">
				<form id="downloadForm">
					<div class="bs-docs-grid">
						<div class="row show-grid">
							<div class="span4">
								<label class="">Recolor Icons</label>
								<input type="text" name="color" id="recolor" data-color="#474A56" style="width: 200px">
							</div>
						</div>
						<div class="row show-grid">
							<div class="span4">
								<label class="checkbox inline">
									<input type="checkbox"value="svg" checked>
									SVG</label>
							</div>
						</div>
						<div class="row show-grid">
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="16" checked>
									16x16</label>
							</div>
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="64">
									64x64</label>
							</div>
						</div>
						<div class="row show-grid">
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="24" checked>
									24x24</label>
							</div>
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="128" >
									128x128</label>
							</div>
						</div>
						<div class="row show-grid">
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="32" checked>
									32x32</label>
							</div>
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="256" >
									256x256</label>
							</div>
						</div>
						<div class="row show-grid">
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="56" >
									56x56</label>
							</div>
							<div class="span2">
								<label class="checkbox inline">
									<input type="checkbox"value="512">
									512x512</label>
							</div>
						</div>
				</form>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">
					Close
				</button>
				<button class="btn btn-primary" id="genZip">
					Generate Zip
				</button>
			</div>
		</div>
		</div>
		
		<div class="modal hide" id="pleaseWaitDialog" data-backdrop="static" data-keyboard="false">
			<div class="modal-header">
				<h1>Processing...</h1>
			</div>
			<div class="modal-body">
				<div class="progress progress-striped active">
					<div class="bar" style="width: 100%;"></div>
				</div>
			</div>
		</div>
			
	</body>
</html>