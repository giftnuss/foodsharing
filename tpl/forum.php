<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restlos Glücklich! - Forum</title>
	<link rel="favicon" href="favicon.ico" type="image/x-icon" />
	<?php echo $head; ?>
	
	<style type="text/css"><?php echo $g_add_css; ?></style>
	
	<script type="text/javascript">
		var _gaq = _gaq || [];  _gaq.push(['_setAccount', 'UA-43313114-1']);  _gaq.push(['_setDomainName', 'lebensmittelretten.de']);  _gaq.push(['_trackPageview']); (function() {var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;   ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);  })();
	</script>
	<script type="text/javascript">
	<?php echo JSMin::minify($g_js_func); ?>
	$(document).ready(function(){
		<?php echo JSMin::minify($js); ?>
	});
	</script>

</head>
<body<?php echo $g_body_class; ?>>
<!-- <div class="ajax-loader"><img src="../images/469.gif" alt="loader" /></div> -->
<?php getDebugging(); ?>
<div id="top">
		<div class="inner">
			<div class="pure-g">
				<div class="pure-u-1">
					<div id="layout_logo" style="float:left;"><a href="/?page=dashboard"><img alt="foodsharing" src="css/gen/img/logo.png"></a></div>
					<?php echo $msgbar; ?>
					<?php echo $mobilemenu; ?>
					<div style="display:none;" class="menu">
							<?php echo $menu ?>
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>
</div>

<iframe id="#forum" src="http://forum.lebensmittelretten.de/"></iframe>
<?php printHidden(); ?>
<div class="pulse-msg ui-shadow" id="pulse-error" style="display:none;"></div>
<div class="pulse-msg ui-shadow" id="pulse-info" style="display:none;"></div>
</body>
</html>