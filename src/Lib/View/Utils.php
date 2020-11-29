<?php

namespace Foodsharing\Lib\View;

use Foodsharing\Lib\Session;
use Foodsharing\Utility\DataHelper;
use Foodsharing\Utility\IdentificationHelper;
use Foodsharing\Utility\PageHelper;
use Foodsharing\Utility\RouteHelper;
use Foodsharing\Utility\Sanitizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class Utils
{
	private $id;
	private Session $session;
	private \Twig\Environment $twig;
	private Sanitizer $sanitizerService;
	private PageHelper $pageHelper;
	private RouteHelper $routeHelper;
	private IdentificationHelper $identificationHelper;
	private DataHelper $dataHelper;
	private TranslatorInterface $translator;

	public function __construct(
		Sanitizer $sanitizerService,
		PageHelper $pageHelper,
		RouteHelper $routeHelper,
		IdentificationHelper $identificationHelper,
		DataHelper $dataHelper,
		TranslatorInterface $translator
	) {
		$this->id = []; // TODO shouldn't this be a string?
		$this->sanitizerService = $sanitizerService;
		$this->pageHelper = $pageHelper;
		$this->routeHelper = $routeHelper;
		$this->identificationHelper = $identificationHelper;
		$this->dataHelper = $dataHelper;
		$this->translator = $translator;
	}

	/**
	 * @required
	 */
	public function setSession(Session $session): void
	{
		$this->session = $session;
	}

	/**
	 * @required
	 */
	public function setTwig(\Twig\Environment $twig): void
	{
		$this->twig = $twig;
	}

	public function v_quickform(string $title, array $elements, array $option = []): string
	{
		return $this->v_field('<div class="v-form">' . $this->v_form($title, $elements, $option) . '</div>', $title);
	}

	public function v_regionPicker(array $region, string $label): string
	{
		$id = $this->identificationHelper->id('bezirk_id');
		$region = $region ?: [
			'id' => 0,
			'name' => $this->translator->trans('region.none'),
		];

		$this->pageHelper->addJs('$("#' . $id . '-button").button().on("click", function () {
			$("#' . $id . '-dialog").dialog("open");
		});');
		$this->pageHelper->addJs('$("#' . $id . '-dialog").dialog({
			autoOpen: false,
			modal: true,
			title: "' . $this->translator->trans('region.change') . '",
			buttons: {
				"' . $this->translator->trans('button.apply') . '": function () {
					$("#' . $id . '").val($("#' . $id . '-hId").val());
					$("#' . $id . '-preview").html($("#' . $id . '-hName").val());
					$("#' . $id . '-dialog").dialog("close");
				}
			}
		});');

		$nodeselect = 'node.data.type == 1 || node.data.type == 2 || node.data.type == 3 || node.data.type == 7 || node.data.type == 9';
		if ($this->session->may('orga')) {
			$nodeselect = 'true';
		}

		$this->pageHelper->addJs('$("#' . $id . '-tree").dynatree({
			onSelect: function (select, node) {
				$("#' . $id . '-hidden").html("");
				$.map(node.tree.getSelectedNodes(), function (node) {
					if (' . $nodeselect . ') {
						$("#' . $id . '-hId").val(node.data.ident);
						$("#' . $id . '").val(node.data.ident);
						$("#' . $id . '-hName").val(node.data.title);
					} else {
						node.select(false);
						pulseError("' . $this->translator->trans('region.no-huge') . '");
					}
				});
			},
			persist: false,
			checkbox: true,
			selectMode: 1,
			initAjax: {
				url: "/xhr.php?f=bezirkTree",
				data: {p: "0"}
			},
			onLazyRead: function (node) {
				node.appendAjax({url: "/xhr.php?f=bezirkTree",
					data: {"p": node.data.ident},
					dataType: "json",
					success: function (node) {},
					error: function (node, XMLHttpRequest, textStatus, errorThrown) {},
					cache: false
				});
			}
		});');
		$this->pageHelper->addHidden('<div id="' . $id . '-dialog"><div id="' . $id . '-tree"></div></div>');

		return $this->v_input_wrapper($label,
			'<span id="' . $id . '-preview">' . $region['name'] . '</span> '
			. '<span id="' . $id . '-button">' . $this->translator->trans('region.change') . '</span>'
			. '<input type="hidden" name="' . $id . '" id="' . $id . '" value="' . $region['id'] . '" />'
			. '<input type="hidden" name="' . $id . '-hName" id="' . $id . '-hName" value="' . $region['id'] . '" />'
			. '<input type="hidden" name="' . $id . 'hId" id="' . $id . '-hId" value="' . $region['id'] . '" />'
		);
	}

	private function v_statusMessage(string $type, string $msg, string $title, string $icon): string
	{
		$title = $title ? '<strong>' . $title . '</strong> ' : '';

		return '<div class="msg-inside ' . $type . '">' . $icon . ' ' . $title . $msg . '</div>';
	}

	/**
	 * @deprecated Before using this in new code, please consider bootstrap-vue alerts instead:
	 * https://bootstrap-vue.org/docs/components/alert
	 */
	public function v_success(string $msg, string $title = '', string $icon = ''): string
	{
		$icon = $icon ? $icon : '<i class="fas fa-check-circle"></i>';

		return $this->v_statusMessage('success', $msg, $title, $icon);
	}

	/**
	 * @deprecated Before using this in new code, please consider bootstrap-vue alerts instead:
	 * https://bootstrap-vue.org/docs/components/alert
	 */
	public function v_info(string $msg, string $title = '', string $icon = ''): string
	{
		$icon = $icon ? $icon : '<i class="fas fa-info-circle"></i>';

		return $this->v_statusMessage('info', $msg, $title, $icon);
	}

	/**
	 * @deprecated Before using this in new code, please consider bootstrap-vue alerts instead:
	 * https://bootstrap-vue.org/docs/components/alert
	 */
	public function v_error(string $msg, string $title = '', string $icon = ''): string
	{
		$icon = $icon ? $icon : '<i class="fas fa-exclamation-triangle"></i>';

		return $this->v_statusMessage('error', $msg, $title, $icon);
	}

	// TODO clean up $value type handling
	public function v_form_time(string $id, $value = false): string
	{
		if ($value == false) {
			$value = [];
			$value['hour'] = 20;
			$value['min'] = 0;
		} elseif (!is_array($value)) {
			$v = explode(':', $value);
			$value = ['hour' => $v[0], 'min' => $v[1]];
		}
		$id = $this->identificationHelper->id($id);
		$hours = range(0, 23);
		$mins = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55];

		$out = '<select name="' . $id . '[hour]">';

		foreach ($hours as $h) {
			$sel = '';
			if ($h == $value['hour']) {
				$sel = ' selected="selected"';
			}
			$out .= '<option' . $sel . ' value="' . $h . '">' . sprintf('%02d', $h) . '</option>';
		}
		$out .= '</select>';

		$out .= '<select name="' . $id . '[min]">';

		foreach ($mins as $m) {
			$sel = '';
			if ($m == $value['min']) {
				$sel = ' selected="selected"';
			}
			$out .= '<option' . $sel . ' value="' . $m . '">' . sprintf('%02d', $m) . '</option>';
		}
		$out .= '</select>' . $this->translator->trans('date.time', ['{time}' => '']);

		return $out;
	}

	public function v_form_tinymce(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);
		$label = $option['label'] ?? $this->translator->trans($id);
		$value = $this->dataHelper->getValue($id);

		$this->pageHelper->addStyle('div#content {width: 580px;} div#right {width: 222px;}');

		$css = 'css/content.css,css/jquery-ui.css';
		$class = 'ui-widget ui-widget-content ui-padding';
		if (isset($option['public_content'])) {
			$class = 'post';
		}

		$plugins = ['autoresize', 'link', 'image', 'media', 'table', 'paste', 'code', 'advlist', 'autolink', 'lists', 'charmap', 'print', 'preview', 'hr', 'anchor', 'pagebreak', 'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'insertdatetime', 'nonbreaking', 'directionality', 'emoticons', 'textcolor'];
		$toolbar = ['styleselect', 'bold italic', 'alignleft aligncenter alignright', 'bullist outdent indent', 'media image link', 'paste', 'code'];
		$addOpt = '';

		if (isset($option['type']) && $option['type'] == 'email') {
			$css = 'css/email.css';
			$class = '';
		}

		$js = '
		$("#' . $id . '").tinymce({
			script_url: "./assets/tinymce/tinymce.min.js",
			theme: "modern",
			language: "de",
			content_css: "' . $css . '",
			body_class: "' . $class . '",
			menubar: false,
			statusbar: false,
			plugins: "' . implode(' ', $plugins) . '",
			toolbar: "' . implode(' | ', $toolbar) . '",
			relative_urls: false,
			valid_elements: "a[href|name|target=_blank|class|style],span,strong,b,div[align|class],br,i,p[class],ul[class],li[class],ol,h1,h2,h3,h4,h5,h6,table,tr,td[valign=top|align|style],th,tbody,thead,tfoot,img[src|width|name|class]",
			convert_urls: false' . $addOpt . '
		});';

		$this->pageHelper->addJs($js);

		return $this->v_input_wrapper($label, '<textarea name="' . $id . '" id="' . $id . '">' . $value . '</textarea>', $id, $option);
	}

	public function v_form_hidden(string $name, $value): string
	{
		$id = $this->identificationHelper->id($name);

		return '<input type="hidden" id="' . $id . '" name="' . $name . '" value="' . $value . '" />';
	}

	public function v_photo_edit($src, $fsid = false): string
	{
		if (!$fsid) {
			$fsid = (int)$this->session->id();
		}
		$id = $this->identificationHelper->id('fotoupload');

		$original = explode('_', $src);
		$original = end($original);

		$this->pageHelper->addJs('
			$("#' . $id . '-link").fancybox({
				minWidth: 600,
				scrolling: "auto",
				closeClick: false,
				helpers: {
					overlay: {closeClick: false}
				}
			});

			$("a[href=\'#edit\']").on("click", function () {

				$("#' . $id . '-placeholder").html(\'<img src="images/' . $original . '" />\');
				$("#' . $id . '-link").trigger("click");
				$.fancybox.reposition();
				jcrop = $("#' . $id . '-placeholder img").Jcrop({
					setSelect: [100, 0, 400, 400],
					aspectRatio: 35 / 45,
					onSelect: function (c) {
						$("#' . $id . '-x").val(c.x);
						$("#' . $id . '-y").val(c.y);
						$("#' . $id . '-w").val(c.w);
						$("#' . $id . '-h").val(c.h);
					}
				});

				$("#' . $id . '-save").show();
				$("#' . $id . '-save").button().on("click", function () {
					showLoader();
					$("#' . $id . '-action").val("crop");
					$.ajax({
						url: "/xhr.php?f=cropagain",
						data: {
							x: parseInt($("#' . $id . '-x").val()),
							y: parseInt($("#' . $id . '-y").val()),
							w: parseInt($("#' . $id . '-w").val()),
							h: parseInt($("#' . $id . '-h").val()),
							fsid: ' . (int)$fsid . '
						},
						success: function (data) {
							if (data == 1) {
								reload();
							}
						},
						complete: function () {
							hideLoader();
						}
					});
					return false;
				});

				$("#' . $id . '-placeholder").css("height", "auto");
				hideLoader();
				setTimeout(function () {
					$.fancybox.update();
					$.fancybox.reposition();
					$.fancybox.toggle();
				}, 200);
			});

			$("a[href=\'#new\']").on("click", function () {
				$("#' . $id . '-link").trigger("click");
				return false;
			});');

		$this->pageHelper->addHidden('
			<div class="fotoupload popbox" style="display: none;" id="' . $id . '">
				<h3>' . $this->translator->trans('picture_upload_widget.picture_upload') . '</h3>
				<p class="subtitle">' . $this->translator->trans('picture_upload_widget.choose_picture') . '</p>
				<form id="' . $id . '-form" method="post" enctype="multipart/form-data" target="' . $id . '-frame" action="/xhr.php?f=uploadPhoto">
					<input type="file" name="uploadpic" onchange="showLoader(); $(\'#' . $id . '-form\')[0].submit();" />
					<input type="hidden" id="' . $id . '-action" name="action" value="upload" />
					<input type="hidden" id="' . $id . '-x" name="x" value="0" />
					<input type="hidden" id="' . $id . '-y" name="y" value="0" />
					<input type="hidden" id="' . $id . '-w" name="w" value="0" />
					<input type="hidden" id="' . $id . '-h" name="h" value="0" />
					<input type="hidden" id="' . $id . '-file" name="file" value="0" />
					<input type="hidden" name="pic_id" value="' . $id . '" />
				</form>
				<div id="' . $id . '-placeholder" style="margin-top: 15px; margin-bottom: 15px; background-repeat: no-repeat; background-position: center center;">
				</div>
				<a href="#" style="display: none;" id="' . $id . '-save">' . $this->translator->trans('button.save') . '</a>
				<iframe name="' . $id . '-frame" src="" width="1" height="1" style="visibility: hidden;"></iframe>
			</div>');

		if (isset($_GET['pinit'])) {
			$this->pageHelper->addJs('$("#' . $id . '-link").trigger("click");');
		}

		$this->pageHelper->addHidden('<a id="' . $id . '-link" href="#' . $id . '">&nbsp;</a>');

		$menu = [['name' => $this->translator->trans('upload.edit_image'), 'href' => '#edit']];
		if ($_GET['page'] == 'settings') {
			$menu[] = ['name' => $this->translator->trans('upload.new_image'), 'href' => '#new'];
		}

		return '
			<div align="center"><img src="' . $src . '" /></div>
			<div>
			' . $this->v_menu($menu) . '
			</div>';
	}

	public function v_form(string $name, array $elements, array $option = []): string
	{
		if (isset($option['id'])) {
			$id = $this->identificationHelper->makeId($option['id']);
		} else {
			$id = $this->identificationHelper->makeId($name, $this->id);
		}

		if (isset($option['dialog'])) {
			$noclose = '';
			if (isset($option['noclose'])) {
				$noclose = ',
				closeOnEscape: false,
				open: function (event, ui) {
					$(this).parent().children().children(".ui-dialog-titlebar-close").hide();
				}';
			}
			$this->pageHelper->addJs('$("#' . $id . '").dialog({modal: true, title: "' . $name . '"' . $noclose . '});');
		}

		$action = $this->routeHelper->getSelf();
		if (isset($option['action'])) {
			$action = $option['action'];
		}

		$out = '
		<div id="' . $id . '">
			<form method="post" id="' . $id . '-form" class="validate" enctype="multipart/form-data" action="' . $action . '">
				<input type="hidden" name="form_submit" value="' . $id . '" />';

		$out .= join('', $elements);

		if (!isset($option['submit'])) {
			$submitTitle = $this->translator->trans('button.send');
		} else {
			$submitTitle = $option['submit'];
		}

		if ($submitTitle !== null) {
			$out .= '
				<div class="input-wrapper">
					<p><input class="button" type="submit" value="' . $submitTitle . '" /></p>
				</div>';
		}

		$out .= '
			</form>
		</div>';

		$this->pageHelper->addJs('$("#' . $id . '-form").on("submit", function (ev) {
			check = true;
			$("#' . $id . '-form div.required .value").each(function (i, el) {
				input = $(el);
				if (input.val() == "") {
					check = false;
					input.addClass("input-error");
					pulseError($("#" + input.attr("id") + "-error-msg").val());
				}
			});

			if (check == false) {
				ev.preventDefault();
			}
		});');

		$this->id[$id] = true;

		return $out;
	}

	public function v_menu(array $items, string $title = '', array $option = []): string
	{
		$id = $this->identificationHelper->id('vmenu');

		$out = '<ul class="linklist">';

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
			$out .= '<li><a class="ui-corner-all' . $sel . '" href="' . $item['href'] . '"' . $click . '>'
				. $item['name']
				. '</a></li>';
		}

		$out .= '</ul>';

		if (!$title) {
			return '
				<div class="ui-widget ui-widget-content ui-corner-all ui-padding">
					' . $out . '
				</div>';
		}

		return '
			<h3 class="head ui-widget-header ui-corner-top">' . $title . '</h3>
			<div class="ui-widget ui-widget-content ui-corner-bottom margin-bottom ui-padding">
				<div id="' . $id . '">
					' . $out . '
				</div>
			</div>';
	}

	public function v_toolbar(array $option = []): string
	{
		$id = 0;
		if (isset($option['id'])) {
			$id = $option['id'];
		}
		if (isset($option['page'])) {
			$page = $option['page'];
		} else {
			$page = $this->routeHelper->getPage();
		}

		if (isset($_GET['bid'])) {
			$bid = '&bid=' . (int)$_GET['bid'];
		} else {
			$bid = $this->session->getCurrentRegionId();
		}

		$out = '';
		if (!isset($option['types'])) {
			$option['types'] = ['edit', 'delete'];
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
				case 'edit':
					$out .= '<li onclick="goTo(\'/?page=' . $page . '&id=' . $id . '&a=edit\');"'
						. ' title="' . $this->translator->trans('button.edit') . '" class="ui-state-default' . $corner . '">'
						. '<span class="ui-icon ui-icon-wrench"></span>'
						. '</li>';
					break;

				case 'delete':
					if (isset($option['confirmMsg'])) {
						$cmsg = $option['confirmMsg'];
					} else {
						$cmsg = $this->translator->trans('really_delete');
					}
					$link = "'/?page=" . $page . '&a=delete&id=' . $id . "'";
					$out .= '<li class="ui-state-default' . $corner . '"'
						. ' title="' . $this->translator->trans('button.delete') . '"'
						. ' onclick="ifconfirm(' . $link . ',\'' . $this->sanitizerService->jsSafe($cmsg) . '\');">'
						. '<span class="ui-icon ui-icon-trash"></span>'
					. '</li>';
					break;

				default:
					break;
			}
		}

		return '<ul class="toolbar" class="ui-widget ui-helper-clearfix">' . $out . '</ul>';
	}

	public function v_tablesorter($head, $data, array $option = []): string
	{
		$params = [
			'nohead' => isset($option['noHead']) && $option['noHead'],
			'pager' => isset($option['pager']) && $option['pager'],
			'head' => $head,
			'data' => $data
		];

		return $this->twig->render('partials/tablesorter.twig', $params);
	}

	public function v_form_textarea(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);
		if (isset($option['value'])) {
			$value = $option['value'];
		} else {
			$value = $this->dataHelper->getValue($id);
		}

		$value = htmlspecialchars($value);

		$label = $this->translator->trans($id);

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
	public function v_form_checkbox(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);

		if (isset($option['checked'])) {
			$value = $option['checked'];
		} else {
			$value = $this->dataHelper->getValue($id);
		}
		$label = $this->translator->trans($id);

		if (isset($option['values'])) {
			$values = $option['values'];
		} else {
			$values = [];
		}

		$checked = [];
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

	public function v_form_tagselect(string $id, ?string $label = null, ?array $valueOptions = null, ?array $values = null): string
	{
		$label = $label ?? $this->translator->trans($id);

		if (is_null($valueOptions)) {
			$source = 'autocompleteURL: async function (request, response) {
			  let data = null
			  try {
				data = await searchUser(request.term)
			  } catch (e) {
			  }
			  response(data)
			}';
		} else {
			$source = 'autocompleteOptions: {
				source: ' . json_encode($valueOptions) . ',
				minLength: 3
			}';
		}

		$this->pageHelper->addJs('
			$("#' . $id . ' input.tag").tagedit({
				' . $source . ',
				allowEdit: false,
				allowAdd: false,
				animSpeed: 100
			});

			$("#' . $id . '").on("keydown", function (event) {
				if (event.keyCode == 13) {
					event.preventDefault();
					return false;
				}
			});
		');

		$input = '<input type="text" name="' . $id . '[]" value="" class="tag input text value" />';
		$values ??= $this->dataHelper->getValue($id);

		if ($values) {
			$input = '';
			foreach ($values as $v) {
				$input .= '<input type="text" name="' . $id . '[' . $v['id'] . '-a]" value="' . $v['name'] . '" class="tag input text value" />';
			}
		}

		return $this->v_input_wrapper($label, '<div id="' . $id . '">' . $input . '</div>', $id, []);
	}

	public function v_form_file(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);

		$val = $this->dataHelper->getValue($id);
		if (!empty($val)) {
			$val = json_decode($val, true);
			$val = substr($val['name'], 0, 30);
		}

		$this->pageHelper->addJs('
			$("#' . $id . '-button").button().on("click", function () {
				$("#' . $id . '").trigger("click");
			});

			$("#' . $id . '").on("change", function () {
				$("#' . $id . '-info").html($("#' . $id . '").val().split("\\\").pop());
			});'
		);

		$btlabel = $this->translator->trans('upload.choose_file');
		if (isset($option['btlabel'])) {
			$btlabel = $option['btlabel'];
		}

		$out = '<input style="display: block; visibility: hidden; margin-bottom: -23px;" type="file" name="' . $id . '" id="' . $id . '" size="chars" maxlength="100000" /><span id="' . $id . '-button">' . $btlabel . '</span> <span id="' . $id . '-info">' . $val . '</span>';

		return $this->v_input_wrapper($this->translator->trans($id), $out);
	}

	public function v_form_radio(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);
		$label = $this->translator->trans($id);

		$check = $this->jsValidate($option, $id, $label);

		if (isset($option['selected'])) {
			$selected = $option['selected'];
		} else {
			$selected = $this->dataHelper->getValue($id);
		}
		if (isset($option['values'])) {
			$values = $option['values'];
		} else {
			$values = [];
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

	private function jsValidate(array $option, string $id, $name): array
	{
		$out = ['class' => '', 'msg' => []];

		if (isset($option['required'])) {
			$out['class'] .= ' required';
			if (!isset($option['required']['msg'])) {
				$out['msg']['required'] = $this->translator->trans('validate.required', ['{it}' => $name]);
			}
		}

		return $out;
	}

	public function v_form_select(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);
		/* isset instead of array_key_exists does not matter here */
		if (isset($option['selected'])) {
			$selected = $option['selected'];
		} else {
			$selected = $this->dataHelper->getValue($id);
		}
		$label = $this->translator->trans($id);
		$check = $this->jsValidate($option, $id, $label);

		if (isset($option['values'])) {
			$values = $option['values'];
		} else {
			$values = [];
		}

		$out = '<select class="input select value" name="' . $id . '" id="' . $id . '">'
			. '<option value="">' . $this->translator->trans('select') . '</option>';
		if (!empty($values)) {
			foreach ($values as $v) {
				$sel = '';
				if ($selected == $v['id']) {
					$sel = ' selected="selected"';
				}
				$out .= '<option value="' . $v['id'] . '"' . $sel . '>' . $v['name'] . '</option>';
			}
		}
		$out .= '</select>';

		return $this->v_input_wrapper($label, $out, $id, $option);
	}

	public function v_input_wrapper(string $label, string $content, $id = false, array $option = []): string
	{
		if (isset($option['nowrapper'])) {
			return $content;
		}

		if ($id === false) {
			$id = $this->identificationHelper->id('input');
		}
		$star = '';
		$error_msg = '';
		$check = $this->jsValidate($option, $id, $label);

		if (isset($option['required'])) {
			$star = '<span class="req-star"> *</span>';
			if (isset($option['required']['msg'])) {
				$error_msg = $option['required']['msg'];
			} else {
				$error_msg = $this->translator->trans('validate.required', ['{it}' => $label]);
			}
		}

		if (isset($option['label'])) {
			$label = $option['label'];
		}

		if (isset($option['collapse'])) {
			$label = '<i class="fas fa-caret-right"></i> ' . $label;
			$this->pageHelper->addJs('
				$("#' . $id . '-wrapper .element-wrapper").hide();
			');

			$option['click'] = 'collapse_wrapper(\'' . $id . '\')';
		}

		if (isset($option['click'])) {
			$label = '<a href="#" onclick="' . $option['click'] . '; return false;">' . $label . '</a>';
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
		<div style="clear: both;"></div>
		</div>';
	}

	public function v_form_daterange(string $id, string $label = ''): string
	{
		$id = $this->identificationHelper->id($id);

		$this->pageHelper->addJs('
			$(function () {
				$("#' . $id . '_from").datepicker({
					changeMonth: true,
					onClose: function (selectedDate) {
						$("#' . $id . '_to").datepicker("option", "minDate", selectedDate);
					}
				});

				$("#' . $id . '_to").datepicker({
					changeMonth: true,
					onClose: function (selectedDate) {
						$("#' . $id . '_from").datepicker("option", "maxDate", selectedDate);
					}
				});
			});
		');

		return $this->v_input_wrapper(
			$label,
			'<input placeholder="' . $this->translator->trans('date.from') . '" class="input text date value"'
			. ' type="text" id="' . $id . '_from" name="' . $id . '[from]">
			<input placeholder="' . $this->translator->trans('date.to') . '" class="input text date value"'
			. ' type="text" id="' . $id . '_to" name="' . $id . '[to]">',
			$id,
			[]
		);
	}

	public function v_form_date(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);
		$label = $option['label'] ?? $this->translator->trans($id);

		$yearRangeFrom = (isset($option['yearRangeFrom'])) ? $option['yearRangeFrom'] : ((int)date('Y') - 60);
		$yearRangeTo = (isset($option['yearRangeTo'])) ? $option['yearRangeTo'] : ((int)date('Y') + 60);

		$value = $this->dataHelper->getValue($id);

		// additional datepicker config in client/lib/jquery-ui-addons.js
		$this->pageHelper->addJs('$("#' . $id . '").datepicker({
			changeYear: true,
			changeMonth: true,
			dateFormat: "yy-mm-dd",
			yearRange: "' . $yearRangeFrom . ':' . $yearRangeTo . '"
		});');

		return $this->v_input_wrapper(
			$label,
			'<input class="input text date value" type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" />',
			$id,
			$option
		);
	}

	public function v_form_text(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);
		$label = $this->translator->trans($id);

		if (isset($option['value'])) {
			$value = $option['value'];
		} else {
			$value = $this->dataHelper->getValue($id);
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

	public function v_field(string $content, $title = false, array $option = [], ?string $titleIcon = null, ?string $titleSpanId = null): string
	{
		$class = '';
		if (isset($option['class'])) {
			$class = ' ' . $option['class'] . '';
		}

		$corner = 'corner-bottom';
		if ($title !== false) {
			$titleHtml = '<div class="head ui-widget-header ui-corner-top">';
			if ($titleSpanId !== null) {
				$titleHtml .= '<span id="' . $titleSpanId . '">';
			}
			if ($titleIcon) {
				$titleHtml .= '<i class="' . $titleIcon . '"></i> ';
			}
			$titleHtml .= htmlspecialchars($title);
			if ($titleSpanId !== null) {
				$titleHtml .= '</span>';
			}
			$titleHtml .= '</div>';
		} else {
			$titleHtml = '';
			$corner = 'corner-all';
		}

		return '
		<div class="field">
			' . $titleHtml . '
			<div class="ui-widget ui-widget-content ' . $corner . ' margin-bottom' . $class . '">
				' . $content . '
			</div>
		</div>';
	}

	public function v_form_passwd(string $id, array $option = []): string
	{
		$id = $this->identificationHelper->id($id);

		$pl = '';
		if (isset($option['placeholder'])) {
			$pl = ' placeholder="' . $option['placeholder'] . '"';
		}

		return $this->v_input_wrapper($this->translator->trans($id), '<input' . $pl . ' class="input text" type="password" name="' . $id . '" id="' . $id . '" />', $id, $option);
	}

	public function v_getStatusAmpel($status): string
	{
		if (!in_array($status, range(1, 7))) {
			$status = 0;
		}
		$color = 'light';
		switch ($status) {
			case 2:
				$color = 'warn'; break;
			case 3:
			case 5:
				$color = 'success'; break;
			case 4:
			case 7:
				$color = 'danger'; break;
			case 6:
				$color = 'info'; break;
		}

		return '<a href="#" onclick="return false;" title="'
			. $this->translator->trans('storestatus.' . $status)
			. '" class="trafficlight store-trafficlight color-'
			. $color . '"><span>&nbsp;</span></a>';
	}
}
