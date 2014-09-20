<?php
define('ROOT_DIR','../');
require_once '../config.inc.php';
require_once '../lib/db.class.php';
require_once '../lib/Foodsaver.class.php';
require_once '../lib/Manual.class.php';
require_once '../lib/libmail_170.php';
require_once '../lib/func.inc.php';

$scripts = '<script src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="../js/jquery-1.9.1.js"></script>
<script type="text/javascript" src="../js/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="../js/fancybox/jquery.fancybox.js"></script>
<script type="text/javascript" src="../js/jquery.ui.addresspicker.js"></script>
<script type="text/javascript" src="../js/jquery.Jcrop.min.js"></script>
<script type="text/javascript" src="../js/dynatree/jquery.dynatree.js"></script>
<script type="text/javascript" src="../js/register.js"></script> ';

$js_info = '
<div class="ui-widget" id="no-js">
	<div class="ui-state-error ui-corner-all" style="padding: 15px;"> 
		<p>
			<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
				<strong>Achtung:</strong> F&uuml;r diese Seite musst Du Javascript in Deinem Browser aktivieren.
		</p>
	</div>
</div>';

$db = new ManualDb();
session_start();

$_SESSION['upload_name'] = uniqid();

$include = './register.inc.php';

$region = bezirkChooser();
$region_html = $region['html'];


$botschafter = false;
$foodsaver = false;
if(isset($_GET['form']))
{
	if($_GET['form'] == 'botschafter')
	{
		$foodsaver = true;
		$botschafter = true;
	}
	else if($_GET['form'] == 'foodsaver')
	{
		$foodsaver = true;
		$botschafter = false;
	}

}

if(isset($_POST['submitted']))
{	
	$scripts = '';
	$region['js'] = '';
	$js_info = '';
	if($db->addFoodsaver($_POST))
	{
		$include = './register.thanks.inc.php';
	}
	else 
	{
		$include = './register.error.inc.php';
	}
}

function b_botAufgaben()
{
	global $db;
	return $db->getVal('body', 'document', 1);
}

function u_autokennzeichen()
{
	global $db;
	$bund = $db->get_autokennzeichen();
	
	print_r($bund);
	
	$out = '';
	foreach ($bund as $b)
	{
		$out .= '
		<option value="'.$b['id'].'">'.$b['name'].' - '.$b['title'].'</option>';
	}
	
	echo $out;
}

function u_getBundeslandValues()
{
	global $db;
	$bund = $db->getBasics_bundesland();
	
	print_r($bund);
	
	$out = '
		<option value="">Bitte ausw&auml;hlen...</option>';
	foreach ($bund as $b)
	{
		$out .= '
		<option value="'.$b['id'].'">'.$b['name'].'</option>';
	}
	
	echo $out;
}


function bezirkChooser()
{
	$id = 'bezirk_id';
	
	$form = 'fs';
	
	if(isset($_GET['form']) && $_GET['form'] == 'botschafter')
	{
		$form = 'bot';
	}
	
	return array(
		'html'=>'<div id="'.$id.'-tree"></div><input type="hidden" name="'.$id.'" id="'.$id.'" value="" />',
		'js' => '
	$(document).ready(function(){
		$("#'.$id.'-tree").dynatree({
            onSelect: function(select, node) {
				$.map(node.tree.getSelectedNodes(), function(node){
					if(node.data.type == 1 || node.data.type == 2 || node.data.type == 3 || node.data.type == 4 || node.data.type == 7)
					{
						$.ajax({
							url: "../xhrapp.php?app=bezirk&m=regBot",
							data: {bid: node.data.ident,form: "'.$form.'"},
							dataType: "json",
							success: function(ret){
								if(ret.script != undefined)
								{
									$.globalEval(ret.script);
								}
								if(ret.status == 0)
								{
									node.select(false);
								}
							}
						});
						$("#'.$id.'").val(node.data.ident);
					}
					else
					{
						node.select(false);
						error("Sorry, Du kannst nicht als Region ein Land oder ein Bundesland auswählen.");
					}
				});
			},
            persist: false,
			checkbox:true,
			selectMode: 1,
			autoFocus: false,
		    initAjax: {
				url: "../xhr.php?f=bezirkTree",
				data: {p: "0" }
			},
			onLazyRead: function(node){
				 node.appendAjax({url: "../xhr.php?f=bezirkTree",
					data: { "p": node.data.ident },
					dataType: "json",
					success: function(node) {
						
					},
					error: function(node, XMLHttpRequest, textStatus, errorThrown) {
						
					},
					cache: false 
				});
			}
        });
	});
		'
	);
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Restlos Glücklich!</title>
<link rel="favicon" href="../favicon.ico" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="../css/foodsaver/jquery-ui-1.10.3.custom.min.css" />
<link rel="stylesheet" type="text/css" href="../css/register.css" />
<link rel="stylesheet" type="text/css" href="../css/jquery.Jcrop.min.css" />
<link rel="stylesheet" type="text/css" href="../js/fancybox/jquery.fancybox.css" />
<link rel="stylesheet" type="text/css" href="../js/dynatree/skin/ui.dynatree.css" />

<?php echo $scripts; ?>

<script type="text/javascript">
<?php echo $region['js']; ?>
</script>

</head>
<body>

<div id="main">
	<div style="margin-bottom:15px;" id="top" class="ui-widget ui-widget-header ui-corner-bottom">
		<div style="float:left;" id="logo"><img src="../css/img/logo.png" alt="foodsharing" /></div>
		<div style="clear:both"></div>
	</div>

	
	
	<?php echo $js_info; ?>

	
	
	<?php include $include; ?>
	
	<div style="display:none" id="dialog-error"><div style="padding: 15px" class="ui-state-error ui-corner-all">
		<p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span>
		<span class="cnt"></span></p>
	</div></div>
	
</div>
</body>
</html>
