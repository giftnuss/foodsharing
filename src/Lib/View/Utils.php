<?php

namespace Foodsharing\Lib\View;

use Foodsharing\Lib\Func;
use Foodsharing\Lib\Session\S;

class Utils
{
	private $id;
	/**
	 * @var Func
	 */
	private $func;

	/**
	 * @var \Twig\Environment
	 */
	private $twig;

	public function __construct()
	{
		$this->id = array();
	}

	/**
	 * @required
	 */
	public function setFunc(Func $func)
	{
		$this->func = $func;
	}

	/**
	 * @required
	 */
	public function setTwig(\Twig\Environment $twig)
	{
		$this->twig = $twig;
	}

	public function v_quickform($titel, $elements, $option = array())
	{
		return $this->v_field('<div class="v-form">' . $this->v_form($titel, $elements, $option) . '</div>', $titel);
	}

	public function v_scroller($content, $width = '232')
	{
		if ($this->func->isMob()) {
			return $content;
		} else {
			$id = $this->func->id('scroller');
			$this->func->addJs('$("#' . $id . '").slimScroll();');

			return '
				<div id="' . $id . '" class="scroller">
					' . $content . '
				</div>';
		}
	}

	public function v_activeSwitcher($table, $field_id, $active)
	{
		$id = $this->func->id('activeSwitch');

		$this->func->addJs('
			$("#' . $id . ' input").switchButton({
				labels_placement: "right",
				on_label: "' . $this->func->s('on_label') . '",
				off_label: "' . $this->func->s('off_label') . '",
				on_callback: function(){
					showLoader();
					$.ajax({
						url: "xhr.php?f=activeSwitch",
						data:{t:"' . $table . '",id:"' . $field_id . '",value:1},
						method:"get",
						complete:function(){
							hideLoader();
						}
					});
				},
				off_callback:function(){
					showLoader();
					$.ajax({
						url: "xhr.php?f=activeSwitch",
						data:{t:"' . $table . '",id:"' . $field_id . '",value:0},
						method:"get",
						complete:function(){
							hideLoader();
						}
					});
				}
			});
		');

		$onck = ' checked="checked"';
		if ($active == 0) {
			$onck = '';
		}

		return '
				<div id="' . $id . '">
					<input' . $onck . ' type="checkbox" name="' . $id . '" id="' . $id . '-on" value="1" />
				</div>';
	}

	public function v_bezirkChildChooser($id, $options = array())
	{
		$this->func->addJsFunc('
		var u_current_bezirk_type = 0;
		function u_printChildBezirke(element)
		{
				val = element.value + "";

				part = val.split(":");

				var parent = part[0];

				u_current_bezirk_type = part[1];

				if(parent == -1)
				{
					$("#' . $id . '").val("");
					return false;
				}

				if(parent == -2)
				{
					$("#' . $id . '-notAvail").fadeIn();
				}

				$("#' . $id . '").val(element.value);

				el = $(element);

				if(el.next().next().next().next().next().hasClass("childChanger"))
				{
					el.next().next().next().next().next().remove();
				}
				if(el.next().next().next().next().hasClass("childChanger"))
				{
					el.next().next().next().next().remove();
				}
				if(el.next().next().next().hasClass("childChanger"))
				{
					el.next().next().next().remove();
				}
				if(el.next().next().hasClass("childChanger"))
				{
					el.next().next().remove();
				}
				if(el.next().hasClass("childChanger"))
				{
					el.next().remove();
				}

				$("#xv-childbezirk-"+parent).remove();


				showLoader();
				$.ajax({
						dataType:"json",
						url:"xhr.php?f=childBezirke&parent=" + parent,
						success : function(data){
							if(data.status == 1)
							{

								$("#' . $id . '-childs-"+parent).remove();
								$("#' . $id . '-wrapper").append(data.html);
								//$("#' . $id . '").val("");

								//$("select.childChanger").last().append(\'<option style="font-weight:bold;" value="-2">- Meine Region ist nicht dabei -</option>\');

							}
							else
							{

							}
						},
						complete: function(){
							hideLoader();
						}
				});
		}');

		$this->func->addJs('u_printChildBezirke({value:"0:0"});');

		return '<div id="' . $id . '-wrapper"></div><input type="hidden" name="' . $id . '" id="' . $id . '" value="0" />';
	}

	public function v_swapText($id, $value)
	{
		return $this->twig->render('partials/swapText.twig', [
			'id' => $id,
			'value' => $value
		]);
	}

	public function v_bezirkChooser($id = 'bezirk_id', $bezirk = false, $option = array())
	{
		$this->func->addScript('/js/dynatree/jquery.dynatree.js');
		$this->func->addScript('/js/jquery.cookie.js');
		$this->func->addStylesheet('/js/dynatree/skin/ui.dynatree.css');

		if (!$bezirk) {
			//$bezirk = $this->func->getBezirk();
			$bezirk = array(
				'id' => 0,
				'name' => $this->func->s('no_bezirk_choosen')
			);
		}
		$id = $this->func->id($id);

		$this->func->addJs('$("#' . $id . '-button").button().click(function(){
			$("#' . $id . '-dialog").dialog("open");
		});');
		$this->func->addJs('$("#' . $id . '-dialog").dialog({
			autoOpen:false,
			modal:true,
			title:"Bezirk ändern",
			buttons:
			{
				"Übernehmen":function()
				{
					$("#' . $id . '").val($("#' . $id . '-hId").val());
					$("#' . $id . '-preview").html($("#' . $id . '-hName").val());
					$("#' . $id . '-dialog").dialog("close");
				}
			}
		});');

		$nodeselect = 'node.data.type == 1 || node.data.type == 2 || node.data.type == 3 || node.data.type == 4 || node.data.type == 7';
		if (S::may('orga')) {
			$nodeselect = 'true';
		}

		$this->func->addJs('$("#' . $id . '-tree").dynatree({
				onSelect: function(select, node) {
					$("#' . $id . '-hidden").html("");
					$.map(node.tree.getSelectedNodes(), function(node){
						if(' . $nodeselect . ')
						{
							$("#' . $id . '-hId").val(node.data.ident);
							$("#' . $id . '").val(node.data.ident);
							$("#' . $id . '-hName").val(node.data.title);
						}
						else
						{
							node.select(false);
							pulseError("Sorry, Du kannst nicht als Region ein Land oder ein Bundesland auswählen.");
						}

					});
				},
				persist: false,
				checkbox:true,
				selectMode: 1,
				initAjax: {
					url: "xhr.php?f=bezirkTree",
					data: {p: "0" }
				},
				onLazyRead: function(node){
					 node.appendAjax({url: "xhr.php?f=bezirkTree",
						data: { "p": node.data.ident },
						dataType: "json",
						success: function(node) {

						},
						error: function(node, XMLHttpRequest, textStatus, errorThrown) {

						},
						cache: false
					});
				}
			});');
		$this->func->addHidden('<div id="' . $id . '-dialog"><div id="' . $id . '-tree"></div></div>');

		$label = $this->func->s('Stammbezirk');
		if (isset($option['label'])) {
			$label = $option['label'];
		}

		return $this->v_input_wrapper($label, '<span id="' . $id . '-preview">' . $bezirk['name'] . '</span> <span id="' . $id . '-button">Bezirk &auml;ndern</span>
				<input type="hidden" name="' . $id . '" id="' . $id . '" value="' . $bezirk['id'] . '" />
				<input type="hidden" name="' . $id . '-hName" id="' . $id . '-hName" value="' . $bezirk['id'] . '" />
				<input type="hidden" name="' . $id . 'hId" id="' . $id . '-hId" value="' . $bezirk['id'] . '" />');
	}

	public function v_login()
	{
		$username = '';
		$password = '';
		if (getenv('FS_ENV') === 'dev') {
			$username = 'userbot@example.com';
			$password = 'user';
		}

		return '<form id="loginbar" action="/?page=login&ref=%2F%3Fpage%3Ddashboard" method="post">
					<input style="margin-right:4px;" class="input corner-all" type="email" name="login_form[email_address]" value="' . $username . '" placeholder="E-Mail-Adresse" required />
					<input class="input corner-all" type="password" name="login_form[password]" value="' . $password . '" placeholder="Passwort" required />
					<input class="submit corner-right" type="submit" value="&#xf0a9;" />
				</form>';
	}

	public function v_msgBar()
	{
		return '<ul id="infobar">
					<li class="msg">
						<a href="#" onclick="return false;">
							<i class="fa fa-comments"></i><span style="display:none;" class="badge">0</span>
						</a>
						<span style="display:none;" class="linkwrapper corner-all ui-shadow">
							<ul class="linklist conversation-list">
							</ul>
							<a class="more" href="/?page=msg">Alle zeigen</a>
						</span>
					</li>

					<li class="bell">
						<a href="#" onclick="return false;">
							<i class="fa fa-bell"></i><span style="display:none;" class="badge">0</span>
						</a>
						<span style="display:none;" class="linkwrapper corner-all ui-shadow">
							<ul class="linklist conversation-list">
							</ul>
							<!-- <a class="more" href="/?page=msg">Alle zeigen</a> -->
						</span>

					</li>

					<li class="basket">
						<a href="#" onclick="return false;">
							<i class="img-fbasket"></i><span style="display:none;" class="badge">0</span>
						</a>
						<span style="display:none;" class="linkwrapper corner-all ui-shadow">
							<ul class="linklist conversation-list">
							</ul>
							<a class="more" href="#" onclick="ajreq(\'newbasket\',{app:\'basket\'});return false;">Neuen Essenskorb anlegen</a>
						</span>

					</li>
				</ul>

				<div id="searchbar">
				<i class="fa fa-search"></i><input type="text" value="" placeholder="' . $this->func->s('search') . '..." />
				<div class="result-wrapper" style="display:none;">
					<ul class="linklist index"></ul>
					<ul class="linklist result"></ul>
					<ul class="linklist more">
						<li><a class="more" onclick="goTo(\'/?page=search&q=\' + encodeURIComponent($(\'#searchbar input\').val()));return false;" href="#">Alle Ergebnisse</a></li>
					</ul>
				</div>
			</div>';
	}

	public function v_success($msg, $title = false)
	{
		if ($title !== false) {
			$title = '<strong>' . $title . '</strong> ';
		}

		return '
		<div class="msg-inside success">
				<i class="fa fa-check-circle"></i> ' . $title . $msg . '
		</div>';
	}

	public function v_info($msg, $title = false, $icon = '<i class="fa fa-info-circle"></i>')
	{
		if ($title !== false) {
			$title = '<strong>' . $title . '</strong> ';
		}

		return '
		<div class="msg-inside info">
				' . $icon . ' ' . $title . $msg . '
		</div>';
	}

	public function v_error($msg, $title = false)
	{
		if ($title !== false) {
			$title = '<strong>' . $title . '</strong> ';
		}

		return '
		<div class="msg-inside error">
				<i class="fa fa-warning"></i> ' . $title . $msg . '
		</div>';
	}

	public function v_form_time($id, $value = false)
	{
		if ($value == false) {
			$value = array();
			$value['hour'] = 20;
			$value['min'] = 0;
		} elseif (!is_array($value)) {
			$v = explode(':', $value);
			$value = array('hour' => $v[0], 'min' => $v[1]);
		}
		$id = $this->func->id($id);
		$hours = range(0, 23);
		$mins = array(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55);

		$out = '<select name="' . $id . '[hour]">';

		foreach ($hours as $h) {
			$sel = '';
			if ($h == $value['hour']) {
				$sel = ' selected="selected"';
			}
			$out .= '<option' . $sel . ' value="' . $h . '">' . $this->func->preZero($h) . '</option>';
		}
		$out .= '</select>';

		$out .= '<select name="' . $id . '[min]">';

		foreach ($mins as $m) {
			$sel = '';
			if ($m == $value['min']) {
				$sel = ' selected="selected"';
			}
			$out .= '<option' . $sel . ' value="' . $m . '">' . $this->func->preZero($m) . '</option>';
		}
		$out .= '</select> Uhr';

		return $out;
	}

	public function v_dialog_button($id, $label, $option = array())
	{
		$new_id = $this->func->id($id);
		$click = '';
		if (isset($option['click'])) {
			$click = $option['click'] . ';';
		}

		$tclick = '';
		if (isset($option['title'])) {
			$tclick = '$("#dialog_' . $id . '").dialog("option","title","' . $option['title'] . '");';
		}
		$btoption = array();
		if (isset($option['icon'])) {
			$btoption[] = 'icons: {primary: "ui-icon-' . $option['icon'] . '"}';
		}
		if (isset($option['notext'])) {
			$btoption[] = 'text:false';
		}

		$this->func->addJs('$("#' . $new_id . '-button").button({' . implode(',', $btoption) . '}).click(function(){' . $click . $tclick . '$("#dialog_' . $id . '").dialog("open");});');

		return '<span id="' . $new_id . '-button">' . $label . '</span>';
	}

	public function v_form_tinymce($id, $option = array())
	{
		$this->func->addScript('/js/tinymce/jquery.tinymce.min.js');
		$id = $this->func->id($id);
		$label = $this->func->s($id);
		$value = $this->func->getValue($id);

		$this->func->addStyle('div#content {width: 580px;}div#right{width:222px;}');

		$css = 'css/content.css,css/jquery-ui.css';
		$class = 'ui-widget ui-widget-content ui-padding';
		if (isset($option['public_content'])) {
			$class = 'post';
		}

		$plugins = array('autoresize', 'link', 'image', 'media', 'table', 'contextmenu', 'paste', 'code', 'advlist', 'autolink', 'lists', 'charmap', 'print', 'preview', 'hr', 'anchor', 'pagebreak', 'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'insertdatetime', 'nonbreaking', 'directionality', 'emoticons', 'textcolor');
		$toolbar = array('styleselect', 'bold italic', 'alignleft aligncenter alignright', 'bullist outdent indent', 'media image link', 'paste', 'code');
		$addOpt = '';

		if (isset($option['type'])) {
			if ($option['type'] == 'email') {
				$css = 'css/email.css';
				$class = '';
			}
		}

		$js = '
		$("#' . $id . '").tinymce({
			script_url : "./js/tinymce/tinymce.min.js",
			theme : "modern",
			language : "de",
			content_css : "' . $css . '",
			body_class: "' . $class . '",
			menubar: false,
			statusbar: false,
			plugins: "' . implode(' ', $plugins) . '",
			toolbar: "' . implode(' | ', $toolbar) . '",
			relative_urls: false,
			valid_elements : "a[href|name|target=_blank|class|style],span,strong,b,div[align|class],br,i,p[class],ul[class],li[class],ol,h1,h2,h3,h4,h5,h6,table,tr,td[valign=top|align|style],th,tbody,thead,tfoot,img[src|width|name|class]",
			convert_urls: false' . $addOpt . '

		});';

		$this->func->addJs($js);

		return $this->v_input_wrapper($label, '<textarea name="' . $id . '" id="' . $id . '">' . $value . '</textarea>', $id, $option);
	}

	public function v_form_hidden($name, $value)
	{
		$id = $this->func->id($name);

		return '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . $value . '" />';
	}

	public function v_form_recip_chooser_mini()
	{
		$id = 'recip_choose';

		return $this->v_input_wrapper($this->func->s('recip_chooser'), '
			<select class="select" name="' . $id . '" id="' . $id . '">
				<option value="botschafter">Alle Botschafter bundesweit</option>
				<option value="orgateam">Orgateam bundesweit</option>
			</select>');
	}

	public function v_form_recip_chooser()
	{
		$this->func->addScript('/js/dynatree/jquery.dynatree.js');
		$this->func->addScript('/js/jquery.cookie.js');
		$this->func->addStylesheet('/js/dynatree/skin/ui.dynatree.css');

		$id = 'recip_choose';
		$out = '
			<select class="select" name="' . $id . '" id="' . $id . '">
				<option value="all">' . $this->func->s('recip_all') . '</option>
				<option value="newsletter">Alle Newsletter-Abonnenten (mindestens Foodsaver)</option>
				<option value="newsletter_all">Alle Newsletter-Abonnenten (Foodsharer, Foodsaver, alle)</option>

				<option value="newsletter_only_foodsharer">NL Abonnenten NUR Foodsharer</option>
				<option value="botschafter">Alle Botschafter weltweit</option>
				<option value="filialverantwortlich">Alle Filialverantwortlichen weltweit</option>
				<option value="filialbot">Alle Filialverantwortlichen + Botschafter</option>
				<option value="all_no_botschafter">Alle Foodsaver ohne Botschafter</option>
				<option value="orgateam">Orgateam</option>
				<option value="choose">' . $this->func->s('recip_choose_bezirk') . '</option>
				<option value="manual">Manuelle Eingabe</option>
			</select>
			<div id="' . $id . '-hidden" style="display:none">

			</div>
			<div id="' . $id . 'manual-wrapper" style="display:none">
				' . $this->v_form_textarea($id . 'manual') . '
			</div>
			<div id="' . $id . '-tree-wrapper" style="display:none;">
				' . $this->v_info('<strong>Hinweis</strong> Um untergeordnete Bezirke zu markieren, musst Du den Ordner erst öffnen! Sonst: Alle nicht sichtbaren Bezirke bekommen keine Mail.') . '
				<div id="' . $id . '-tree">

				</div>
			</div>';

		$this->func->addJs('
				$(\'#' . $id . '\').change(function(){
					if($(this).val() == "choose" || $(this).val() == "choosebot" || $(this).val() == "filialbez")
					{
						$("#' . $id . '-tree-wrapper").show();
						$("#' . $id . 'manual-wrapper").hide();
					}
					else if($(this).val() == "manual")
					{
						$("#' . $id . 'manual-wrapper").show();
						$("#' . $id . '-tree-wrapper").hide();
					}
					else
					{
						$("#' . $id . 'manual-wrapper").hide();
						$("#' . $id . '-tree-wrapper").hide();
					}

				});

				$("#' . $id . '-tree").dynatree({
				onSelect: function(select, node) {
					$("#' . $id . '-hidden").html("");
					$.map(node.tree.getSelectedNodes(), function(node){
						$("#' . $id . '-hidden").append(\'<input type="hidden" name="' . $id . '-choose[]" value="\'+node.data.ident+\'" />\');
					});
				},
				persist: false,
				checkbox:true,
				selectMode: 3,
				clickFolderMode: 3,
				activeVisible: true,
				initAjax: {
					url: "xhr.php?f=bezirkTree",
					data: {p: "0" }
				},
				onLazyRead: function(node){
					 node.appendAjax({url: "xhr.php?f=bezirkTree",
						data: { "p": node.data.ident },
						dataType: "json",
						success: function(node) {

						},
						error: function(node, XMLHttpRequest, textStatus, errorThrown) {

						},
						cache: false
					});
				}
			});');

		return $this->v_input_wrapper($this->func->s('recip_chooser'), $out);
	}

	public function v_photo_edit($src, $fsid = false)
	{
		if (!$fsid) {
			$fsid = $this->func->fsId();
		}
		$id = $this->func->id('fotoupload');

		$original = explode('_', $src);
		$original = end($original);

		$this->func->addJs('

				$("#' . $id . '-link").fancybox({
					minWidth : 600,
					scrolling :"auto",
					closeClick : false,
					helpers : {
					  overlay : {closeClick: false}
					}
				});

				$("a[href=\'#edit\']").click(function(){

					$("#' . $id . '-placeholder").html(\'<img src="images/' . $original . '" />\');
					$("#' . $id . '-link").trigger("click");
					$.fancybox.reposition();
					jcrop = $("#' . $id . '-placeholder img").Jcrop({
						 setSelect:   [ 100, 0, 400, 400 ],
						 aspectRatio: 35 / 45,
						 onSelect: function(c){
								$("#' . $id . '-x").val(c.x);
								$("#' . $id . '-y").val(c.y);
								$("#' . $id . '-w").val(c.w);
								$("#' . $id . '-h").val(c.h);
						 }
					 });

					 $("#' . $id . '-save").show();
					 $("#' . $id . '-save").button().click(function(){
						 showLoader();
						 $("#' . $id . '-action").val("crop");
						 $.ajax({
							url: "xhr.php?f=cropagain",
							data: {
								x:parseInt($("#' . $id . '-x").val()),
								y:parseInt($("#' . $id . '-y").val()),
								w:parseInt($("#' . $id . '-w").val()),
								h:parseInt($("#' . $id . '-h").val()),
								fsid:' . (int)$fsid . '
							},
							success:function(data){
								if(data == 1)
								{
									reload();
								}
							},
							complete:function(){
								hideLoader();
							}
						 });
						 return false;
					 });

					 $("#' . $id . '-placeholder").css("height","auto");
					 hideLoader();
					 setTimeout(function(){
						 $.fancybox.update();
						 $.fancybox.reposition();
						 $.fancybox.toggle();
					 },200);
				});

				$("a[href=\'#new\']").click(function(){
					$("#' . $id . '-link").trigger("click");
					return false;
				});
				');

		$this->func->addHidden('
				<div class="fotoupload popbox" style="display:none;" id="' . $id . '">
					<h3>Fotoupload</h3>
					<p class="subtitle">Hier kannst Du ein Foto von Deinem Computer ausw&auml;hlen</p>
					<form id="' . $id . '-form" method="post" enctype="multipart/form-data" target="' . $id . '-frame" action="xhr.php?f=uploadPhoto">
						<input type="file" name="uploadpic" onchange="showLoader();$(\'#' . $id . '-form\')[0].submit();" />
						<input type="hidden" id="' . $id . '-action" name="action" value="upload" />
						<input type="hidden" id="' . $id . '-x" name="x" value="0" />
						<input type="hidden" id="' . $id . '-y" name="y" value="0" />
						<input type="hidden" id="' . $id . '-w" name="w" value="0" />
						<input type="hidden" id="' . $id . '-h" name="h" value="0" />
						<input type="hidden" id="' . $id . '-file" name="file" value="0" />
						<input type="hidden" name="pic_id" value="' . $id . '" />
					</form>
					<div id="' . $id . '-placeholder" style="margin-top:15px;margin-bottom:15px;background-repeat:no-repeat;background-position:center center;">

					</div>
					<a href="#" style="display:none" id="' . $id . '-save">Speichern</a>
					<iframe name="' . $id . '-frame" src="upload.php" width="1" height="1" style="visibility:hidden;"></iframe>
				</div>');

		if (isset($_GET['pinit'])) {
			$this->func->addJs('$("#' . $id . '-link").trigger("click");');
		}

		$this->func->addHidden('<a id="' . $id . '-link" href="#' . $id . '">&nbsp;</a>');

		$menu = array(array('name' => $this->func->s('edit_photo'), 'href' => '#edit'));
		if ($_GET['page'] == 'settings') {
			$menu[] = array('name' => $this->func->s('upload_new_photo'), 'href' => '#new');
		}

		return '
			<div align="center"><img src="' . $src . '" /></div>
			<div>
			' . $this->v_menu($menu) . '
			</div>
			<div style="visibility:hidden"><img src="/images/' . $original . '" /></div>';
	}

	public function v_form_info($msg, $label = false)
	{
		return '<div class="input-wrapper">' . $this->v_info($msg, $label) . '</div>';
	}

	public function v_form($name, $elements, $option = array())
	{
		$js = '';
		if (isset($option['id'])) {
			$id = $this->func->makeId($option['id']);
		} else {
			$id = $this->func->makeId($name, $this->id);
		}

		if (isset($option['dialog'])) {
			$noclose = '';
			if (isset($option['noclose'])) {
				$noclose = ',
				closeOnEscape: false,
				open: function(event, ui) {$(this).parent().children().children(".ui-dialog-titlebar-close").hide();}';
			}
			$this->func->addJs('$("#' . $id . '").dialog({modal:true,title:"' . $name . '"' . $noclose . '});');
		}

		$action = $this->func->getSelf();
		if (isset($option['action'])) {
			$action = $option['action'];
		}

		$out = '
		<div id="' . $id . '">
		<form method="post" id="' . $id . '-form" class="validate" enctype="multipart/form-data" action="' . $action . '">
			<input type="hidden" name="form_submit" value="' . $id . '" />';
		foreach ($elements as $el) {
			$out .= $el;
		}

		if (!isset($option['submit'])) {
			$out .= $this->v_form_submit('Senden', $id, $option);
		} elseif ($option['submit'] !== false) {
			$out .= $this->v_form_submit($option['submit'], $id, $option);
		}

		$out .= '
		</div>
		</form>
		';

		$this->func->addJs('$("#' . $id . '-form").submit(function(ev){

			check = true;
			$("#' . $id . '-form div.required .value").each(function(i,el){
				input = $(el);
				if(input.val() == "")
				{
					check = false;
					input.addClass("input-error");
					error($("#" + input.attr("id") + "-error-msg").val());
				}
			});

			if(check == false)
			{
				ev.preventDefault();
			}

		});');

		if (!empty($js)) {
			$out .= '
			<script type="text/javascript">
			$(document).ready(function(){
			' . $js . '
			});
			</script>';
		}

		$this->id[$id] = true;

		return $out;
	}

	public function v_menu($items, $title = false, $option = array())
	{
		$id = $this->func->id('vmenu');

		//$this->func->addJs('$("#'.$id.'").menu();');
		$out = '
		<ul class="linklist">';

		foreach ($items as $item) {
			if (!isset($item['href'])) {
				$item['href'] = '#';
			}

			$click = '';
			if (isset($item['click'])) {
				$click = ' onclick="' . $item['click'] . '"';
			}
			$sel = '';
			if ($item['href'] == '?' . $_SERVER['QUERY_STRING']) {
				$sel = ' active';
			}
			$out .= '
					<li><a class="ui-corner-all' . $sel . '" href="' . $item['href'] . '"' . $click . '>' . $item['name'] . '</a></li>';
		}

		$out .= '
		</ul>';

		if (!$title) {
			return '
				<div class="ui-widget ui-widget-content ui-corner-all ui-padding">
					' . $out . '
				</div>';
		} else {
			return '
				<h3 class="head ui-widget-header ui-corner-top">' . $title . '</h3>
				<div class="ui-widget ui-widget-content ui-corner-bottom margin-bottom ui-padding">
					<div id="' . $id . '">
						' . $out . '
					</div>
				</div>';
		}

		return $out;
	}

	public function v_toolbar($option = array())
	{
		$id = 0;
		if (isset($option['id'])) {
			$id = $option['id'];
		}
		if (isset($option['page'])) {
			$page = $option['page'];
		} else {
			$page = $this->func->getPage();
		}

		if (isset($_GET['bid'])) {
			$bid = '&bid=' . (int)$_GET['bid'];
		} else {
			$bid = $this->func->getBezirkId();
		}

		$out = '';
		if (!isset($option['types'])) {
			$option['types'] = array('edit', 'delete');
		}

		$last = count($option['types']) - 1;

		foreach ($option['types'] as $i => $t) {
			$corner = '';
			if ($i == 0) {
				$corner = ' ui-corner-left';
			}
			if ($i == $last) {
				$corner .= ' ui-corner-right';
			}
			switch ($t) {
				case 'image':
					$out .= '<li onclick="openPhotoDialog(' . $option['id'] . ');" title="Foto Hochladen" class="ui-state-default' . $corner . '"><span class="ui-icon ui-icon-image"></span></li>';
					break;
				case 'new':
					$out .= '<li onclick="goTo(\'/?page=' . $page . '&id=' . $id . '&a=new\');" title="neu" class="ui-state-default' . $corner . '"><span class="ui-icon ui-icon-document"></span></li>';
					break;
				case 'comment':
					$out .= '<li attr="' . $page . ':' . $id . '" title="Notiz hinzuf&uuml;gen" class="toolbar-comment ui-state-default' . $corner . '"><span class="ui-icon ui-icon-comment"></span></li>';
					break;

				case 'edit':
					$out .= '<li onclick="goTo(\'/?page=' . $page . '&id=' . $id . '&a=edit\');" title="bearbeiten" class="ui-state-default' . $corner . '"><span class="ui-icon ui-icon-wrench"></span></li>';
					break;

				case 'delete':
					if (isset($option['confirmMsg'])) {
						$cmsg = $option['confirmMsg'];
					} else {
						$cmsg = 'Wirklich l&ouml;schen?';
					}
					$out .= '<li onclick="ifconfirm(\'/?page=' . $page . '&a=delete&id=' . $id . '\',\'' . $this->func->jsSafe($cmsg) . '\');" title="l&ouml;schen" class="ui-state-default' . $corner . '"><span class="ui-icon ui-icon-trash"></span></li>';
					break;

				default:
					break;
			}
		}

		$out = '<ul class="toolbar" class="ui-widget ui-helper-clearfix">' . $out . '</ul>';

		return $out;
	}

	public function v_tablesorter($head, $data, $option = array())
	{
		$id = $this->func->id('table');

		$style = '';
		if (isset($option['noHead'])) {
			$style = ' style="visibility:hidden;margin:0;paddin:0;height:1px;overflow:hidden;"';
		}

		$out = '
		<div class="tablesort-wrapper">
			<table id="' . $id . '" class="tablesorter">
				<thead' . $style . '>
					<tr class="ui-corner-top"' . $style . '>';

		$i = 0;

		$jsoption = '';
		foreach ($head as $h) {
			$width = '';
			if (isset($h['width'])) {
				$width = ' style="width:' . $h['width'] . 'px"';
			}
			$out .= '
						<th' . $width . '' . $style . ' class="ui-corner-all">' . $h['name'] . '</th>';

			if (isset($h['sort']) && $h['sort'] == false) {
				$jsoption .= $i . ':{sorter:false},';
			}

			++$i;
		}

		$out .= '
					</tr>
				</thead>
				<tbody>';

		foreach ($data as $row) {
			$out .= '
					<tr>';
			foreach ($row as $r) {
				$out .= '
						<td>' . $r['cnt'] . '</td>';
			}
			$out .= '
					</tr>';
		}

		$out .= '
				</tbody>
			</table>
		</div>';

		$this->func->addJs('
			$("table.tablesorter td ul.toolbar").css("visibility","hidden");

			$( "table.tablesorter tbody tr" ).hover(
					function() {
						$( this ).addClass("hover");
						$( this ).children("td:last").children("ul").css("visibility","visible");
					},
					function() {
						$( this ).removeClass("hover");
						$( this ).children("td:last").children("ul").css("visibility","hidden");
					}
			);
		');

		$pager_js = '';
		if (isset($option['pager']) && count($data) > 14) {
			$this->func->addScript('/js/tablesorter/jquery.tablesorter.pager.js');
			$this->func->addStyle('div.pager{position:relative !important;}');

			$this->func->addJs('
				$(".prev").button({
					icons: {
						primary: "ui-icon-circle-arrow-w"
					},
					text: false
				});
				$(".next").button({
					icons: {
						primary: "ui-icon-circle-arrow-e"
					},
					text: false
				});
			');

			$out .= '
			<div id="' . $id . '-pager" class="pager ui-corner-all">
				<form>
					<!--<a class="first" href="#">&nbsp;</a>-->
					<a class="prev">&nbsp;</a>

					<input style="display:none" type="text" class="pagedisplay"/>
					<span class="pagedisplay2">
						<span>Seite</span> <span class="seite"></span> <span>von</span> <span class="anz"></span>
					</span>
					<a class="next">&nbsp;</a>
					<!-- <img src="http://tablesorter.com/addons/pager/icons/last.png" class="last"/> -->
					<span class="pagesize-wrapper">
						<select class="pagesize">
							<option selected="selected"  value="10">10</option>
							<option value="20">20</option>
							<option value="30">30</option>
							<option  value="40">40</option>
						</select> <span>Einträge pro Seite</span>
					</span>
				</form>
			</div>';

			$pager_js = '.tablesorterPager({container: $("#' . $id . '-pager")})';
		}

		if (!empty($jsoption)) {
			$jsoption = 'headers:{' . substr($jsoption, 0, (strlen($jsoption) - 1)) . '}';

			$this->func->addJs('$("#' . $id . '").tablesorter({
					' . $jsoption . ',
					widgets: ["zebra"]
				})' . $pager_js . ';');
		} else {
			$this->func->addJs('$("#' . $id . '").tablesorter({widgets: ["zebra"]})' . $pager_js . ';');
		}

		return $out;
	}

	public function v_form_submit($val, $id, $option = array())
	{
		$out = '';
		if (isset($option['buttons'])) {
			foreach ($option['buttons'] as $b) {
				$out .= $b;
			}
		}

		return '
		<div class="input-wrapper">
			<p><input class="button" type="submit" value="' . $val . '" />' . $out . '</p>
		</div>';
	}

	public function v_form_textarea($id, $option = array())
	{
		$id = $this->func->id($id);
		if (isset($option['value'])) {
			$value = $option['value'];
		} else {
			$value = $this->func->getValue($id);
		}

		$value = htmlspecialchars($value);

		$label = $this->func->s($id);

		$style = '';
		if (isset($option['style'])) {
			$style = ' style="' . $option['style'] . '"';
		}

		$maxlength = '';
		if (isset($option['maxlength'])) {
			$maxlength = ' maxlength="' . (int)$option['maxlength'] . '"';
		}

		$ph = '';
		if (isset($option['placeholder'])) {
			$ph = ' placeholder="' . $option['placeholder'] . '"';
		} elseif (isset($option['maxlength'])) {
			$ph = ' placeholder="maximal ' . $option['maxlength'] . ' Zeichen..."';
		}

		return $this->v_input_wrapper(
			$label,
			'<textarea' . $style . $maxlength . $ph . ' class="input textarea value" name="' . $id . '" id="' . $id . '">' . $value . '</textarea>',
			$id,
			$option
		);
	}

	/*
	 * This method outputs a checkbox input with different possibilities on how to define values and checked values.
	 *
	 * for example:
	 * $g_data[$id => ['list', 'of', 'checked', 'values']]
	 *
	 * $option = ['values' => ['list', 'of', 'possible', 'values']];
	 */
	public function v_form_checkbox($id, $option = array())
	{
		$id = $this->func->id($id);

		if (isset($option['checked'])) {
			$value = $option['checked'];
		} else {
			$value = $this->func->getValue($id);
		}
		$label = $this->func->s($id);

		if (isset($option['values'])) {
			$values = $option['values'];
		} else {
			$values = array();
		}

		$checked = array();
		if (is_array($value)) {
			foreach ($value as $key => $ch) {
				$checked[$ch] = true;
			}
		} elseif ($value == 1) {
			$checked[1] = true;
		}
		$out = '';
		if (!empty($values)) {
			foreach ($values as $v) {
				$sel = '';
				if (isset($checked[$v['id']]) || isset($option['checkall'])) {
					$sel = ' checked="checked"';
				}
				$v['name'] = trim($v['name']);
				if (!empty($v['name'])) {
					$out .= '
					<label><input class="input cb-' . $id . '" type="checkbox" name="' . $id . '[]" value="' . $v['id'] . '"' . $sel . ' />&nbsp;' . $v['name'] . '</label><br />';
				}
			}
		}

		return $this->v_input_wrapper($label, $out, $id, $option);
	}

	public function v_form_tagselect($id, $option = array())
	{
		$xhr = $id;
		if (isset($option['xhr'])) {
			$xhr = $option['xhr'];
		}

		$url = 'get' . ucfirst($xhr);
		if (isset($option['url'])) {
			$url = $option['url'];
		}

		$source = 'autocompleteURL: "xhr.php?f=' . $url . '"';
		$post = '';

		if (isset($option['valueOptions'])) {
			$source = 'autocompleteOptions: {source: ' . json_encode($option['valueOptions']) . ',minLength: 0}';
		}

		$this->func->addJs('
			$("#' . $id . ' input.tag").tagedit({
				' . $source . ',
				allowEdit: false,
				allowAdd: false,
				animSpeed:100
			});

			$("#' . $id . '").keydown(function(event){
				if(event.keyCode == 13) {
				  event.preventDefault();
				  return false;
				}
			  });
		');

		$input = '<input type="text" name="' . $id . '[]" value="" class="tag input text value" />';
		if (isset($option['values'])) {
			$values = $option['values'];
		} else {
			$values = $this->func->getValue($id);
		}
		if ($values) {
			$input = '';
			foreach ($values as $v) {
				$input .= '<input type="text" name="' . $id . '[' . $v['id'] . '-a]" value="' . $v['name'] . '" class="tag input text value" />';
			}
		}

		return $this->v_input_wrapper($this->func->s($id), '<div id="' . $id . '">' . $input . '</div>', $id, $option);
	}

	public function v_form_picture($id, $option = array())
	{
		$id = $this->func->id($id);

		$this->func->addJs('
			$("#' . $id . '-link").fancybox({
				minWidth : 600,
				scrolling :"auto",
				closeClick : false,
				helpers : {
				  overlay : {closeClick: false}
				}
			});

			$("#' . $id . '-opener").button().click(function(){

				$("#' . $id . '-link").trigger("click");

			});
		');

		$options = '';

		$crop = '0';
		if (isset($option['crop'])) {
			$crop = '1';
			$options .= '<input type="hidden" id="' . $id . '-ratio" name="ratio" value="' . json_encode($option['crop']) . '" />';
			$options .= '<input type="hidden" id="' . $id . '-ratio-i" name="ratio-i" value="0" />';
			$options .= '<input type="hidden" id="' . $id . '-ratio-val" name="ratio-val" value="[]" />';
		}

		if (isset($option['resize'])) {
			$options .= '<input type="hidden" id="' . $id . '-resize" name="resize" value="' . json_encode($option['resize']) . '" />';
		}

		$this->func->addHidden('
		<div id="' . $id . '-fancy">
			<div class="popbox">
				<h3>' . $this->func->s($id) . ' Upload</h3>
				<p class="subtitle">W&auml;hle ein Bild von Deinem Rechner</p>

				<form id="' . $id . '-form" method="post" enctype="multipart/form-data" target="' . $id . '-iframe" action="xhr.php?f=uploadPicture&id=' . $id . '&crop=' . $crop . '">

					<input type="file" name="uploadpic" onchange="showLoader();$(\'#' . $id . '-form\')[0].submit();" />

					<input type="hidden" id="' . $id . '-action" name="action" value="uploadPicture" />
					<input type="hidden" id="' . $id . '-id" name="id" value="' . $id . '" />

					<input type="hidden" id="' . $id . '-x" name="x" value="0" />
					<input type="hidden" id="' . $id . '-y" name="y" value="0" />
					<input type="hidden" id="' . $id . '-w" name="w" value="0" />
					<input type="hidden" id="' . $id . '-h" name="h" value="0" />

					' . $options . '

				</form>

				<div id="' . $id . '-crop"></div>

				<iframe src="" id="' . $id . '-iframe" name="' . $id . '-iframe" style="width:1px;height:1px;visibility:hidden;"></iframe>
			</div>
		</div>');

		$thumb = '';

		$pic = (isset($option['pic']) ? $option['pic'] : $this->func->getValue($id));
		if (!empty($pic)) {
			$thumb = '<img src="images/' . str_replace('/', '/thumb_', $pic) . '" />';
		}
		$out = '
			<input type="hidden" name="' . $id . '" id="' . $id . '" value="" /><div id="' . $id . '-preview">' . $thumb . '</div>
			<span id="' . $id . '-opener">' . $this->func->s('upload_picture') . '</span><span style="display:none;"><a href="#' . $id . '-fancy" id="' . $id . '-link">&nbsp;</a></span>';

		return $this->v_input_wrapper($this->func->s($id), $out);
	}

	public function v_form_file($id, $option = array())
	{
		$id = $this->func->id($id);

		$val = $this->func->getValue($id);
		if (!empty($val)) {
			$val = json_decode($val, true);
			$val = substr($val['name'], 0, 30);
		}

		$this->func->addJs('
		$("#' . $id . '-button").button().click(function(){$("#' . $id . '").click();});
		$("#' . $id . '").change(function(){$("#' . $id . '-info").html($("#' . $id . '").val().split("\\\").pop());});');

		$btlabel = $this->func->s('choose_file');
		if (isset($option['btlabel'])) {
			$btlabel = $option['btlabel'];
		}

		$out = '<input style="display:block;visibility:hidden;margin-bottom:-23px;" type="file" name="' . $id . '" id="' . $id . '" size="chars" maxlength="100000" /><span id="' . $id . '-button">' . $btlabel . '</span> <span id="' . $id . '-info">' . $val . '</span>';

		return $this->v_input_wrapper($this->func->s($id), $out);
	}

	public function v_form_list($id, $option = array())
	{
		$id = $this->func->id($id);
		$value = $this->func->getValue($id);
		$label = $this->func->s($id);

		$out = '<textarea class="input textarea value" name="' . $id . '" id="' . $id . '">';

		$val = '';
		if (is_array($value)) {
			foreach ($value as $v) {
				$out .= $v['name'] . "\r\n";
			}
		}

		$out .= '</textarea>';

		return $this->v_input_wrapper($label, $out, $id, $option);
	}

	public function v_form_radio($id, $option = array())
	{
		$id = $this->func->id($id);
		$label = $this->func->s($id);

		$check = $this->func->jsValidate($option, $id, $label);

		if (isset($option['selected'])) {
			$selected = $option['selected'];
		} else {
			$selected = $this->func->getValue($id);
		}
		if (isset($option['values'])) {
			$values = $option['values'];
		} else {
			$values = array();
		}

		$disabled = '';
		if (isset($option['disabled']) && $option['disabled'] === true) {
			$disabled = 'disabled="disabled" ';
		}

		$out = '';
		if (!empty($values)) {
			foreach ($values as $v) {
				$sel = '';
				if ($selected == $v['id']) {
					$sel = ' checked="checked"';
				}
				$out .= '
				<label><input name="' . $id . '" type="radio" value="' . $v['id'] . '"' . $sel . ' ' . $disabled . '/>' . $v['name'] . '</label><br />';
			}
		}
		$out .= '';

		return $this->v_input_wrapper($label, $out, $id, $option);
	}

	public function v_form_select($id, $option = array())
	{
		$id = $this->func->id($id);
		/* isset instead of array_key_exists does not matter here */
		if (isset($option['selected'])) {
			$selected = $option['selected'];
		} else {
			$selected = $this->func->getValue($id);
		}
		$label = $this->func->s($id);
		$check = $this->func->jsValidate($option, $id, $label);

		if (isset($option['values'])) {
			$values = $option['values'];
		} else {
			$values = array();
		}

		$out = '
		<select class="input select value" name="' . $id . '" id="' . $id . '">
			<option value="">Bitte ausw&auml;hlen...</option>';
		if (!empty($values)) {
			foreach ($values as $v) {
				$sel = '';
				if ($selected == $v['id']) {
					$sel = ' selected="selected"';
				}
				$out .= '
				<option value="' . $v['id'] . '"' . $sel . '>' . $v['name'] . '</option>';
			}
		}
		$out .= '
		</select>';

		if (isset($option['add'])) {
			$this->func->addHidden('
			<div id="' . $id . '-dialog" style="display:none;">
				' . $this->v_form_text($id . ': NEU') . '
			</div>');

			$out .= '<a href="#" id="' . $id . '-add" class="select-add">&nbsp;</a>';

			$this->func->addJs('

					$("#' . $id . 'neu").keyup(function(e){

						if(e.keyCode == 13)
						{
						  addSelect("' . $id . '");
						}
					});



					$("#' . $id . '-add").button({
						icons:{primary:"ui-icon-plusthick"},
						text:false
					}).click(function(event){

						event.preventDefault();
						$("#' . $id . '-dialog label").remove();

						$("#' . $id . '-dialog").dialog({
							modal:true,
							title: "' . $label . ' anlegen",
							buttons:
							{
								"Speichern":function()
								{
									addSelect("' . $id . '");
								}
							}
						});
					});


					');
		}

		return $this->v_input_wrapper($label, $out, $id, $option);
	}

	public function v_input_wrapper($label, $content, $id = false, $option = array())
	{
		if (isset($option['nowrapper'])) {
			return $content;
		}

		if ($id === false) {
			$id = $this->func->id('input');
		}
		$class = '';
		$star = '';
		$error_msg = '';
		$check = $this->func->jsValidate($option, $id, $label);

		if (isset($option['required'])) {
			$star = '<span class="req-star"> *</span>';
			if (isset($option['required']['msg'])) {
				$error_msg = $option['required']['msg'];
			} else {
				$error_msg = $label . ' darf nicht leer sein';
			}
		}

		if (isset($option['label'])) {
			$label = $option['label'];
		}

		if (isset($option['collapse'])) {
			$label = '<i class="fa fa-caret-right"></i> ' . $label;
			$this->func->addJs('
				$("#' . $id . '-wrapper .element-wrapper").hide();
			');

			$option['click'] = 'collapse_wrapper(\'' . $id . '\')';
		}

		if (isset($option['click'])) {
			$label = '<a href="#" onclick="' . $option['click'] . ';return false;">' . $label . '</a>';
		}

		$label_in = '<label class="wrapper-label ui-widget" for="' . $id . '">' . $label . $star . '</label>';
		if (isset($option['nolabel'])) {
			$label_in = '';
		}

		$desc = '';
		if (isset($option['desc'])) {
			$desc = '<div class="desc">' . $option['desc'] . '</div>';
		}

		if (isset($option['class'])) {
			$check['class'] .= ' ' . $option['class'];
		}

		return '
		<div class="input-wrapper' . $check['class'] . '" id="' . $id . '-wrapper">
		' . $label_in . '
		' . $desc . '
		<div class="element-wrapper">
			' . $content . '
		</div>
		<input type="hidden" id="' . $id . '-error-msg" value="' . $error_msg . '" />
		<div style="clear:both;"></div>
		</div>';
	}

	public function v_form_daterange($id = 'daterange', $option = array())
	{
		$label = $this->func->s($id);
		$id = $this->func->id($id);

		if (!isset($option['options'])) {
			$option['options'] = array('from' => array(), 'to' => array());
		}

		$this->func->addJs('
			 $(function() {
				$( "#' . $id . '_from" ).datepicker({
					changeMonth: true,
					onClose: function( selectedDate ) {
						$( "#' . $id . '_to" ).datepicker( "option", "minDate", selectedDate );
					},
					' . implode(',', $option['options']['from']) . '
				});
				$( "#' . $id . '_to" ).datepicker({
					changeMonth: true,
					onClose: function( selectedDate ) {
						$( "#' . $id . '_from" ).datepicker( "option", "maxDate", selectedDate );
					}
					' . implode(',', $option['options']['to']) . '
				});
			});
		');

		if (!isset($option['content_after'])) {
			$option['content_after'] = '';
		}

		return $this->v_input_wrapper(
			$label,
			'
			<input placeholder="' . $this->func->s('from') . '" class="input text date value" type="text" id="' . $id . '_from" name="' . $id . '[from]">
			<input placeholder="' . $this->func->s('to') . '" class="input text date value" type="text" id="' . $id . '_to" name="' . $id . '[to]">' . $option['content_after'],
			$id,
			$option
		);
	}

	public function v_form_date($id, $option = array())
	{
		$id = $this->func->id($id);
		$label = $this->func->s($id);

		$yearRangeFrom = (isset($option['yearRangeFrom'])) ? $option['yearRangeFrom'] : (date('Y') - 60);
		$yearRangeTo = (isset($option['yearRangeTo'])) ? $option['yearRangeTo'] : (date('Y') + 60);

		$value = $this->func->getValue($id);

		$this->func->addJs('$("#' . $id . '").datepicker({
			changeYear: true,
			changeMonth: true,
			dateFormat: "yy-mm-dd",
			monthNames: [ "Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember" ],
			yearRange: "' . $yearRangeFrom . ':' . $yearRangeTo . '"
		});');

		return $this->v_input_wrapper(
			$label,
			'<input class="input text date value" type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" />',
			$id,
			$option
		);
	}

	public function v_form_text($id, $option = array())
	{
		$id = $this->func->id($id);
		$label = $this->func->s($id);

		if (isset($option['value'])) {
			$value = $option['value'];
		} else {
			$value = $this->func->getValue($id);
		}

		$value = htmlspecialchars($value);

		$disabled = '';
		if (isset($option['disabled']) && $option['disabled']) {
			$disabled = 'readonly="readonly"';
		}

		$pl = '';
		if (isset($option['placeholder'])) {
			$pl = ' placeholder="' . $option['placeholder'] . '"';
		}

		return $this->v_input_wrapper(
			$label,
			'<input' . $pl . ' class="input text value" type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" ' . $disabled . '/>',
			$id,
			$option
		);
	}

	public function v_field($content, $title = false, $option = array())
	{
		$class = '';
		if (isset($option['class'])) {
			$class = ' ' . $option['class'] . '';
		}

		$corner = 'corner-bottom';
		if ($title !== false) {
			$title = '<div class="head ui-widget-header ui-corner-top">' . $title . '</div>';
		} else {
			$title = '';
			$corner = 'corner-all';
		}

		return '
		<div class="field">
			' . $title . '
			<div class="ui-widget ui-widget-content ' . $corner . ' margin-bottom' . $class . '">
				' . $content . '
			</div>
		</div>';
	}

	public function v_form_passwd($id, $option = array())
	{
		$id = $this->func->id($id);

		$pl = '';
		if (isset($option['placeholder'])) {
			$pl = ' placeholder="' . $option['placeholder'] . '"';
		}

		return $this->v_input_wrapper($this->func->s($id), '<input' . $pl . ' class="input text" type="password" name="' . $id . '" id="' . $id . '" />', $id, $option);
	}

	public function v_getMessages($error, $info)
	{
		$out = '';
		if (count($error) > 0) {
			$out .= '
			<div class="ui-widget pageblock ui-padding">
			<div class="ui-state-error ui-corner-all">
			<p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-alert"></span>';

			foreach ($error as $e) {
				$out .= $this->func->qs($e) . '<br />';
			}

			$out .= '
			</div>
			</div>';
		}

		if (count($info) > 0) {
			$out .= '
			<div class="ui-widget pageblock">
			<div class="ui-state-highlight ui-corner-all ui-padding">
			<p><span style="float: left; margin-right: .3em;" class="ui-icon ui-icon-info"></span>';

			foreach ($info as $i) {
				$out .= $this->func->qs($i) . '<br />';
			}

			$out .= '
			</div>
			</div>';
		}

		return $out;
	}

	public function buttonset($buttons = array())
	{
		$id = $this->func->id('buttonset');
		$out = '
		<div id="' . $id . '">';

		$i = 0;
		foreach ($buttons as $b) {
			++$i;
			$bid = $this->func->makeId($b['name']);
			$out .= '
			<a href="#" id="' . $id . '-' . $bid . '">' . $b['name'] . '</a>';
		}

		$out .= '
		</div>';
	}

	public function v_switch($views = array())
	{
		$out = '<select class="v-switch"  onchange="goTo(this.value);">
					<!--<option value="#">Ansicht:</option>-->';

		foreach ($views as $v) {
			$id = $this->func->makeId($v);
			$sel = '';
			if (isset($_GET['v']) && $id == $_GET['v']) {
				$sel = ' selected="selected"';
			}
			$out .= '
					<option value="' . $this->func->addGet('v', $id) . '"' . $sel . '>' . $v . '</option>';
		}

		return $out . '
				</select>';
	}

	public function v_getStatusAmpel($status)
	{
		$out = '';
		switch ($status) {
			case 1:
				$out = '<span class="hidden">1</span><a href="#" onclick="return false;" title="Es besteht noch kein Kontakt" class="ampel ampel-grau"><span>&nbsp;</span></a>';
				break;
			case 2:
				$out = '<span class="hidden">2</span><a href="#" onclick="return false;" title="Verhandlungen laufen" class="ampel ampel-gelb"><span>&nbsp;</span></a>';
				break;
			case 3:
				$out = '<span class="hidden">3</span><a href="#" onclick="return false;" title="Betrieb kooperiert bereits" class="ampel ampel-gruen"><span>&nbsp;</span></a>';
				break;
			case 5:
				$out = '<span class="hidden">3</span><a href="#" onclick="return false;" title="Betrieb kooperiert bereits" class="ampel ampel-gruen"><span>&nbsp;</span></a>';
				break;
			case 4:
				$out = '<span class="hidden">4</span><a href="#" onclick="return false;" title="Will nicht kooperieren" class="ampel ampel-rot"><span>&nbsp;</span></a>';
				break;
			case 6:
				$out = '<span class="hidden">4</span><a href="#" onclick="return false;" title="Spendet an Tafel etc. und wirft nichts weg" class="ampel ampel-blau"><span>&nbsp;</span></a>';
				break;
		}

		return $out;
	}
}
