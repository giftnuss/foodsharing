<?php

namespace Foodsharing\Lib\Xhr;

use Exception;
use Flourish\fImage;
use Foodsharing\Lib\Db\Mem;
use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Model;
use Foodsharing\Modules\Foodsaver\FoodsaverModel;
use Foodsharing\Modules\Mailbox\MailboxModel;
use Foodsharing\Modules\Message\MessageModel;
use Foodsharing\Modules\Region\RegionModel;
use Foodsharing\Modules\Store\StoreModel;

class XhrMethods
{
	private $model;
	private $func;
	private $v_utils;

	/**
	 * XhrMethods constructor.
	 *
	 * @param $model
	 */
	public function __construct(Model $model)
	{
		global $g_func;
		$this->func = $g_func;
		global $g_view_utils;
		$this->v_utils = $g_view_utils;
		$this->model = $model;
	}

	public function xhr_verify($data)
	{
		$fsmodel = new FoodsaverModel();
		$bids = $fsmodel->getFsBezirkIds((int)$data['fid']);

		if ($this->func->isBotForA($bids, false, true) || $this->func->isOrgaTeam()) {
			if ($countver = $this->model->qOne('SELECT COUNT(*) FROM ' . PREFIX . 'verify_history WHERE date BETWEEN NOW()- INTERVAL 20 SECOND AND now() AND bot_id = ' . $this->func->fsId() . '')) {
				if ($countver > 10) {
					return json_encode(array(
						'status' => 0
					));
				}
			}

			$countFetch = $this->model->qOne('
			SELECT 	count(a.`date`)
			FROM   `' . PREFIX . 'abholer` a

			WHERE a.foodsaver_id = ' . (int)$data['fid'] . '
			AND   a.`date` > NOW()
		');

			if ($countFetch > 0) {
				return json_encode(array(
					'status' => 0
				));
			}

			if ($this->model->update('UPDATE `' . PREFIX . 'foodsaver` SET `verified` = ' . (int)$data['v'] . ' WHERE `id` = ' . (int)$data['fid'])) {
				$this->model->insert('
			INSERT INTO 	`' . PREFIX . 'verify_history`
			(
				`fs_id`,
				`date`,
				`bot_id`,
				`change_status`
			)
			VALUES
			(
				' . (int)$data['fid'] . ',
				NOW(),
				' . $this->func->fsId() . ',
				' . (int)$data['v'] . '
			)
		');
				$model = new Model();
				$model->delBells('new-fs-' . (int)$data['fid']);

				return json_encode(array(
					'status' => 1
				));
			}
		} else {
			return json_encode(array(
				'status' => 0
			));
		}
	}

	public function xhr_getPinPost($data)
	{
		$this->func->incLang('betrieb');
		$this->func->incLang('fsbetrieb');

		if ($this->model->isInTeam($data['bid']) || $this->func->isBotschafter() || $this->func->isOrgaTeam()) {
			if ($out = $this->model->q('
				SELECT 	n.id,
						n.`text`,
						fs.name,
						fs.id AS fsid,
						UNIX_TIMESTAMP(n.zeit) AS zeit,
						fs.photo,
						n.milestone
									
				FROM  	' . PREFIX . 'betrieb_notiz n,
						' . PREFIX . 'foodsaver fs
				
				WHERE fs.id = n.foodsaver_id
				AND n.betrieb_id = ' . (int)$data['bid'] . '
				
				ORDER BY n.zeit DESC
				
				LIMIT 50')
			) {
				//$out = array_reverse($out);
				$html = '<table class="pintable">';
				$odd = 'odd';
				foreach ($out as $o) {
					if ($odd == 'odd') {
						$odd = 'even';
					} else {
						$odd = 'odd';
					}
					$pic = $this->func->img($o['photo']);

					$delete = '';
					if ($this->func->isOrgaTeam() || $this->func->fsId() == $o['fsid']) {
						$delete = '<span class="dot">·</span><a class="pdelete light" href="#p' . $o['id'] . '" onclick="u_delPost(' . (int)$o['id'] . ');return false;">' . $this->func->s('delete') . '</a>';
					}

					$msg = '<span class="msg">' . nl2br($o['text']) . '</span>
						<div class="foot">
							<span class="time">' . $this->func->format_dt($o['zeit']) . ' von ' . $o['name'] . '</span>' . $delete . '
						</div>';

					if ($o['milestone'] == 1) {
						$odd .= ' milestone';

						$msg = '
					<div class="milestone">
						<a href="#" onclick="profile(' . (int)$o['fsid'] . ');return false;">' . $o['name'] . '</a> ' . $this->func->sv('betrieb_added', $this->func->format_d($o['zeit'])) . '
					</div>';

						$pic = 'img/milestone.png';
					} elseif ($o['milestone'] == 2) {
						$odd .= ' milestone';
						$msg = '<span class="msg">' . $this->func->sv('accept_request', '<a href="#" onclick="profile(' . (int)$o['fsid'] . ');return false">' . $this->model->getVal('name', 'foodsaver', $o['fsid']) . '</a>') . '</span>';
					} elseif ($o['milestone'] == 3) {
						$odd .= ' milestone';
						$pic = 'img/milestone.png';
						$msg = '<span class="msg"><strong>' . $this->func->sv('status_change_at', $this->func->format_d($o['zeit'])) . '</strong> ' . $this->func->s($o['text']) . '</span>';
					} elseif ($o['milestone'] == 5) {
						$odd .= ' milestone';
						$msg = '<span class="msg">' . $this->func->sv('quiz_dropped', '<a href="#" onclick="profile(' . (int)$o['fsid'] . ');return false">' . $this->model->getVal('name', 'foodsaver', $o['fsid']) . '</a>') . '</span>';
					}

					$html .= '
					<tr class="' . $odd . ' bpost bpost-' . $o['id'] . '">
						<td class="img"><a href="#"><img src="' . $pic . '" onclick="profile(' . (int)$o['fsid'] . ');return false;" /></a></td>
						<td>' . $msg . '</td>
					</tr>';
				}

				return json_encode(array(
					'status' => 1,
					'html' => $html . '</table>'
				));
			}
		}

		return json_encode(array(
			'status' => 0
		));
	}

	public function xhr_activeSwitch($data)
	{
		$allowed = array(
			'blog_entry' => true
		);

		if ($this->func->may()) {
			if (isset($allowed[$data['t']])) {
				if ($this->model->update('UPDATE `' . PREFIX . $data['t'] . '` SET `active` = ' . (int)$data['value'] . ' WHERE `id` = ' . (int)$data['id'])) {
					return 1;
				}
			}
		}

		return 0;
	}

	public function xhr_grabInfo($data)
	{
		if (S::may()) {
			Mem::delPageCache('/?page=dashboard');
			$fields = $this->func->unsetAll($data, array('photo_public', 'lat', 'lon', 'stadt', 'plz', 'anschrift'));

			if ($this->model->updateFields($fields, 'foodsaver', $this->func->fsId())) {
				return $this->xhr_out();
			}
		}
	}

	public function xhr_addPinPost($data)
	{
		if ($this->model->isInTeam($data['bid']) || $this->func->isOrgaTeam() || $this->func->isBotschafter()) {
			if (isset($_SESSION['last_pinPost'])) {
				if ((time() - $_SESSION['last_pinPost']) < 2) {
					return $this->xhr_getPinPost($data);
				}
			}
			if ($this->model->add_betrieb_notiz(array(
				'foodsaver_id' => $this->func->fsId(),
				'betrieb_id' => $data['bid'],
				'text' => $data['text'],
				'zeit' => date('Y-m-d H:i:s'),
				'milestone' => 0,
				'last' => 1
			))
			) {
				$poster = $this->model->getVal('name', 'foodsaver', $this->func->fsId());
				$betrieb = $this->model->getVal('name', 'betrieb', (int)$data['bid']);

				$model = new StoreModel();
				$model->addBell($data['team'], 'store_wallpost_title', 'store_wallpost', 'img img-store brown', array(
					'href' => '/?page=fsbetrieb&id=' . (int)$data['bid']
				), array(
					'user' => S::user('name'),
					'name' => $betrieb
				), 'store-wallpost-' . (int)$data['bid']);
				$_SESSION['last_pinPost'] = time();

				return $this->xhr_getPinPost($data);
			}
		}
	}

	public function xhr_childBezirke($data)
	{
		if (isset($data['parent'])) {
			$sql = ' AND 		`type` != 7';
			if ($this->func->isOrgaTeam()) {
				$sql = '';
			}
			if ($childs = $this->model->q('SELECT `id`,`parent_id`,`has_children`,`name`,`type` FROM `' . PREFIX . 'bezirk` WHERE `parent_id` = ' . $this->model->intval($data['parent']) . $sql)) {
				return json_encode(array(
					'status' => 1,
					'html' => ViewUtils::childBezirke($childs, $data['parent'])
				));
			} else {
				return json_encode(array(
					'status' => 0
				));
			}
		}
	}

	public function xhr_profile($data)
	{
		$foodsaver = $this->model->getOne_foodsaver($data['id']);

		$bezirk = $this->model->getBezirk($foodsaver['bezirk_id']);

		//print_r($foodsaver);

		$subtitle = '';
		if (isset($foodsaver['botschafter'])) {
			$subtitle = 'ist ' . $this->func->genderWord($foodsaver['geschlecht'], 'Botschafter', 'Botschafterin', 'Botschafter/in') . ' f&uuml;r ';
			foreach ($foodsaver['botschafter'] as $i => $b) {
				$sep = ', ';

				if ($i == (count($foodsaver['botschafter']) - 2)) {
					$sep = ' und ';
				}

				$subtitle .= $b['name'] . $sep;
			}

			$subtitle = substr($subtitle, 0, (strlen($subtitle) - 2));
			if ($foodsaver['orgateam'] == 1) {
				$subtitle .= ', außerdem engagiert ' . $this->func->genderWord($foodsaver['geschlecht'], 'er', 'sie', 'er/sie') . ' sich Foodsharing Orgateam';
			}
		} elseif ($foodsaver['bezirk_id'] == 0) {
			$subtitle = 'hat sich bisher für keinen Bezirk entschieden.';
		} else {
			$subtitle = 'ist ' . $this->func->genderWord($foodsaver['geschlecht'], 'Foodsaver', 'Foodsaverin', 'Foodsaver') . ' für ' . $bezirk['name'];
		}

		$photo = $this->func->img($foodsaver['photo'], 130, 'q');
		$data = array();

		if (($this->func->isBotschafter() || $this->func->isOrgaTeam() || isset($foodsaver['botschafter']))) {
			if (!empty($foodsaver['handy'])) {
				$data[] = array('name' => 'Handy', 'val' => $foodsaver['handy']);
			}
			if (!empty($foodsaver['telefon'])) {
				$data[] = array('name' => 'Telefon', 'val' => $foodsaver['telefon']);
			}
			if ($this->func->isOrgaTeam()) {
				$data[] = array('name' => 'E-Mail-Adresse', 'val' => $foodsaver['email']);
				$data[] = array('name' => 'Adresse', 'val' => $foodsaver['anschrift'] . '<br />' . $foodsaver['plz'] . ' ' . $foodsaver['stadt']);
			}
		}

		$about = array();
		$about[] = array('name' => 'Rolle', 'val' => $foodsaver['name'] . ' ' . $subtitle);

		if (strlen($foodsaver['about_me_public']) > 3) {
			$about[] = array('name' => 'Über ' . $foodsaver['name'], 'val' => $foodsaver['about_me_public']);
		}

		$pers = ViewUtils::set($about, $foodsaver['name'] . ' ' . $foodsaver['nachname']);

		$thead = '';
		$tbody = '';

		if ($this->func->isOrgaTeam()) {
			$fsdata = json_decode($foodsaver['data'], true);
			$detail = '';

			if (isset($fsdata['from_google'])) {
				$fsdata = $fsdata['from_google'];

				foreach ($fsdata as $key => $v) {
					if (is_array($v)) {
						$v = '<pre>' . print_r($v, true) . '</pre>';
					}
					$detail .= '<p>' . $this->func->s($key) . ':<br />' . $v . '</p>';
				}

				$detail = $this->v_utils->v_input_wrapper('Daten vom Google-Formular', $detail);
			} else {
				$detail = $this->v_utils->v_input_wrapper('Daten aus Anmeldeformular', '<pre>' . print_r($fsdata, true) . '</pre>');
			}

			$thead = '<li><a href="#ptab-' . (int)$foodsaver['id'] . '-2">Details</a></li>';
			$tbody = '
		<div id="ptab-' . (int)$foodsaver['id'] . '-2">
			<div style="overflow:auto;height:400px;">
				<pre>' . $detail . '</pre>
			</div>
		</div>';
		}

		$edit = '';
		if ($this->func->isOrgaTeam() || $this->func->isBotschafter()) {
			$edit = '<li><a href="/?page=foodsaver&a=edit&id=' . $foodsaver['id'] . '">bearbeiten</a></li>';
		}

		return json_encode(array(
			'status' => 1,
			'html' => '
			<div id="dialog-profile-info">
				<div id="tabs-profile">
			    	<ul>
			      		<li><a href="#ptab-' . (int)$foodsaver['id'] . '-1">' . $foodsaver['name'] . '</a></li>
						' . $thead . '
			    	</ul>
			    	<div id="ptab-' . (int)$foodsaver['id'] . '-1">
					<div class="xv_left">
						<img src="' . $photo . '" alt="' . $foodsaver['name'] . ' ' . $foodsaver['nachname'] . '" />
						<ul>
							<li><a onclick="chat(' . (int)$foodsaver['id'] . ');return false;" href="#">Nachricht schreiben</a></li>
							' . $edit . '
						</ul>
					</div>
					
					' . ViewUtils::set($data, 'Kontaktdaten') . '
					<div style="clear:both;"></div>
						' . $pers . '
					</div>
				</div>',

			'script' => ''
		));
	}

	public function xhr_jsonTeam($data)
	{
		$fs = $this->model->q(' SELECT fs.`id`,CONCAT(fs.`name`," ",fs.`nachname`) AS name FROM ' . PREFIX . 'foodsaver fs WHERE `active` = 1 ');

		return 'var foodsaver = ' . json_encode($fs);
	}

	public function xhr_jsonBetriebe($data)
	{
		$b = '';
		if (($this->func->isBotschafter() || $this->func->isOrgaTeam() || S::may('fs') || isset($foodsaver['botschafter']))) {
			$b = $this->model->q(' SELECT `id`,lat,lon FROM ' . PREFIX . 'betrieb WHERE lat != "" ');
		}

		return 'var g_betriebe = ' . json_encode($b);
	}

	public function xhr_bBubble($data)
	{
		if (S::may('fs')) {
			if ($b = $this->model->getMyBetrieb($data['id'])) {
				$b['inTeam'] = false;
				$b['pendingRequest'] = false;
				if ($this->model->isInTeam($b['id'])) {
					$b['inTeam'] = true;
				}
				if ($this->model->hasAnfrageAtStore($b['id'])) {
					$b['pendingRequest'] = true;
				}

				return json_encode(array(
					'status' => 1,
					'html' => ViewUtils::bBubble($b),
					'betrieb' => array(
						'name' => $b['name']
					)
				));
			}
		}

		return json_encode(array('status' => 0));
	}

	public function xhr_fsBubble($data)
	{
		if ($b = $this->model->getOne_foodsaver($data['id'])) {
			return json_encode(array(
				'status' => 1,
				'html' => ViewUtils::fsBubble($b)
			));
		}

		return json_encode(array('status' => 0));
	}

	public function xhr_jsonBoth($data)
	{
		return $this->xhr_jsonFoodsaver($data) . "\n" . $this->xhr_jsonBetriebe($data);
	}

	public function xhr_jsonFoodsaver($data)
	{
		$fs = '';
		if (($this->func->isBotschafter() || $this->func->isOrgaTeam() || S::may('fs') || isset($foodsaver['botschafter']))) {
			$fs = $this->model->q(' SELECT `id`, `photo_public`,`lat`,`lon` FROM `' . PREFIX . 'foodsaver` WHERE `active` = 1 AND lat != "" ');
		}

		return 'var foodsaver = ' . json_encode($fs);
	}

	public function xhr_loadMarker($data)
	{
		$out = array();
		$out['status'] = 0;
		if (isset($data['types']) && is_array($data['types'])) {
			$out['status'] = 1;
			foreach ($data['types'] as $t) {
				if ($t == 'foodsaver') {
					$out['foodsaver'] = $this->model->q(' SELECT `id`, `photo_public`,`lat`,`lon` FROM `' . PREFIX . 'foodsaver` WHERE `active` = 1 AND rolle IN(1,2,3,4) AND `photo_public` != 4 AND lat != "" ');
				} elseif ($t == 'betriebe') {
					$team_status = array();
					$nkoorp = '';
					if (isset($data['options']) && is_array($data['options'])) {
						foreach ($data['options'] as $opt) {
							if ($opt == 'needhelpinstant') {
								$team_status[] = 'team_status = 2';
							} elseif ($opt == 'needhelp') {
								$team_status[] = 'team_status = 1';
							} elseif ($opt == 'nkoorp') {
								$nkoorp = ' AND betrieb_status_id NOT IN(3,5)';
							}
						}
					}

					if (!empty($team_status)) {
						$team_status = ' AND (' . implode(' OR ', $team_status) . ')';
					} else {
						$team_status = '';
					}

					$out['betriebe'] = $this->model->q(' SELECT `id`,lat,lon FROM ' . PREFIX . 'betrieb WHERE lat != "" ' . $team_status . $nkoorp);
				} elseif ($t == 'botschafter') {
					$out['botschafter'] = $this->model->q(' 
						
						SELECT DISTINCT fs.`id`, fs.`photo_public`,fs.`lat`,fs.`lon` 
						FROM 	`' . PREFIX . 'foodsaver` fs,
								`' . PREFIX . 'botschafter` b,
								`' . PREFIX . 'bezirk` bz
						 
						WHERE 	fs.`id` = b.`foodsaver_id` 
						AND 	b.bezirk_id = bz.id
						AND 	lat != "" 
						AND 	bz.`type` != 7 
				');
				} elseif ($t == 'fairteiler') {
					$out['fairteiler'] = $this->model->q(' SELECT `id`,lat,lon,bezirk_id AS bid FROM ' . PREFIX . 'fairteiler WHERE lat != "" AND status = 1 ');
				} elseif ($t == 'baskets') {
					if ($baskets = $this->model->q('
				
					SELECT id, lat, lon, location_type
					FROM ' . PREFIX . 'basket
					WHERE `status` = 1
					
				')
					) {
						/*
						foreach ($baskets as $key => $b)
						{
							if($b['location_type'] !== 0)
							{
								//
							}
						}
						*/
						$out['baskets'] = $baskets;
					}
				}
			}
		}

		return json_encode($out);
	}

	public function xhr_addComment($data)
	{
		return $this->model->addComment($data);
	}

	public function xhr_uploadPicture($data)
	{
		$func = '';
		$id = strtolower($data['id']);
		$id = preg_replace('/[^a-z0-9_]/', '', $id);
		if (isset($_FILES['uploadpic'])) {
			if ($this->func->is_allowed($_FILES['uploadpic'])) {
				$datein = str_replace('.jpeg', '.jpg', strtolower($_FILES['uploadpic']['name']));
				$ext = strtolower(substr($datein, strlen($datein) - 4, 4));

				$path = ROOT_DIR . 'images/' . $id;

				if (!is_dir($path)) {
					mkdir($path);
				}

				$newname = uniqid() . $ext;

				move_uploaded_file($_FILES['uploadpic']['tmp_name'], $path . '/orig_' . $newname);

				copy($path . '/orig_' . $newname, $path . '/' . $newname);
				$image = new fImage($path . '/' . $newname);
				$image->resize(600, 0);

				$image->saveChanges();

				if ($_GET['crop'] == 1) {
					$func = 'pictureCrop';
				} elseif (isset($_POST['resize'])) {
					return $this->pictureResize(array(
						'img' => $newname,
						'id' => $id,
						'resize' => $_POST['resize']
					));
				}

				return '<html><head></head><body onload="parent.' . $func . '(\'' . $id . '\',\'' . $newname . '\');"></body></html>';

				echo uniqid();
			}
		}
	}

	public function xhr_cropagain($data)
	{
		if ($photo = $this->model->getVal('photo', 'foodsaver', $data['fsid'])) {
			$path = ROOT_DIR . 'images';
			$img = $photo;

			$targ_w = $data['w'];
			$targ_h = $data['h'];
			$jpeg_quality = 100;

			$ext = explode('.', $img);
			$ext = end($ext);
			$ext = strtolower($ext);

			switch ($ext) {
				case 'gif':
					$img_r = imagecreatefromgif($path . '/' . $img);
					break;
				case 'jpg':
					$img_r = imagecreatefromjpeg($path . '/' . $img);
					break;
				case 'png':
					$img_r = imagecreatefrompng($path . '/' . $img);
					break;
				default:
					$img_r = null;
			}

			$dst_r = @imagecreatetruecolor($targ_w, $targ_h);

			if (!$dst_r) {
				return '0';
			}

			imagecopyresampled($dst_r, $img_r, 0, 0, $data['x'], $data['y'], $targ_w, $targ_h, $data['w'], $data['h']);

			$new_path = $path . '/crop_' . $img;

			@unlink($new_path);

			switch ($ext) {
				case 'gif':
					imagegif($dst_r, $new_path);
					break;
				case 'jpg':
					imagejpeg($dst_r, $new_path, $jpeg_quality);
					break;
				case 'png':
					imagepng($dst_r, $new_path, 0);
					break;
			}

			copy($path . '/' . $img, $path . '/thumb_' . $img);
			$image = new fImage($path . '/thumb_' . $img);
			$image->resize(150, 0);
			$image->saveChanges();

			copy($path . '/' . $img, $path . '/thumb_crop_' . $img);
			$image = new fImage($path . '/thumb_crop_' . $img);
			$image->resize(200, 0);
			$image->saveChanges();

			return '1';
		}

		return '0';
	}

	public function xhr_pictureCrop($data)
	{
		$data['img'];
		$data['id'];
		/*
		 * [ratio-val] => [{"x":37,"y":87,"w":500,"h":281},{"x":64,"y":0,"w":450,"h":450}]
		  [resize] => [250,528]
		 */

		$ratio = json_decode($_POST['ratio-val'], true);
		$resize = json_decode($_POST['resize']);

		if (is_array($ratio) && is_array($resize)) {
			foreach ($ratio as $i => $r) {
				$this->func->cropImg(ROOT_DIR . 'images/' . $data['id'], $data['img'], $i, $r['x'], $r['y'], $r['w'], $r['h']);
				foreach ($resize as $r) {
					copy(ROOT_DIR . 'images/' . $data['id'] . '/crop_' . $i . '_' . $data['img'], ROOT_DIR . 'images/' . $data['id'] . '/crop_' . $i . '_' . $r . '_' . $data['img']);
					$image = new fImage(ROOT_DIR . 'images/' . $data['id'] . '/crop_' . $i . '_' . $r . '_' . $data['img']);
					$image->resize($r, 0);
					$image->saveChanges();
				}
			}

			copy(ROOT_DIR . 'images/' . $data['id'] . '/' . $data['img'], ROOT_DIR . 'images/' . $data['id'] . '/thumb_' . $data['img']);
			$image = new fImage(ROOT_DIR . 'images/' . $data['id'] . '/thumb_' . $data['img']);
			$image->resize(150, 0);
			$image->saveChanges();

			return '<html><head></head><body onload="parent.pictureReady(\'' . $data['id'] . '\',\'' . $data['img'] . '\');"></body></html>';
		}
	}

	private function pictureResize($data)
	{
		$id = $data['id'];
		$img = $data['img'];
		$resize = json_decode($data['resize'], true);

		if (is_array($resize)) {
			foreach ($resize as $r) {
				copy(ROOT_DIR . 'images/' . $id . '/' . $img, ROOT_DIR . 'images/' . $id . '/' . $r . '_' . $img);
				$image = new fImage(ROOT_DIR . 'images/' . $id . '/' . $r . '_' . $img);
				$image->resize($r, 0);
				$image->saveChanges();
			}
		}

		copy(ROOT_DIR . 'images/' . $id . '/' . $img, ROOT_DIR . 'images/' . $id . '/thumb_' . $img);
		$image = new fImage(ROOT_DIR . 'images/' . $id . '/thumb_' . $img);
		$image->resize(150, 0);
		$image->saveChanges();

		return '<html><head></head><body onload="parent.pictureReady(\'' . $id . '\',\'' . $img . '\');"></body></html>';
	}

	public function xhr_out($html = '', $js = '')
	{
		return json_encode(array(
			'status' => 1,
			'html' => $html,
			'script' => $js
		));
	}

	public function xhr_getFoodsaver($data)
	{
		return $this->xhr_getRecip($data);
	}

	public function xhr_getRecip($data)
	{
		if ($this->func->may()) {
			$fs = $this->model->xhrGetFoodsaver($data);

			return json_encode($fs);
		}
	}

	public function xhr_addPhoto($data)
	{
		$data = $this->func->getPostData();

		if (isset($data['fs_id'])) {
			$user_id = (int)$data['fs_id'];

			if (isset($_FILES['photo']) && (int)$_FILES['photo']['size'] > 0) {
				$ext = explode('.', $_FILES['photo']['name']);
				$ext = strtolower(end($ext));

				@unlink('./images/' . $user_id . '.' . $ext);

				$file = $this->func->makeUnique() . '.' . $ext;
				if (move_uploaded_file($_FILES['photo']['tmp_name'], './images/' . $file)) {
					$image = new fImage('./images/' . $file);
					$image->resize(800, 800);
					$image->saveChanges();

					copy('./images/' . $file, './images/thumb_crop_' . $file);
					copy('./images/' . $file, './images/crop_' . $file);

					$image = new fImage('./images/thumb_crop_' . $file);
					$image->cropToRatio(35, 45);
					$image->resize(200, 200);
					$image->saveChanges();

					$image = new fImage('./images/crop_' . $file);
					$image->cropToRatio(35, 45);
					$image->resize(600, 600);
					$image->saveChanges();

					copy('./images/thumb_crop_' . $file, './images/mini_q_' . $file);
					$image = new fImage('./images/mini_q_' . $file);
					$image->cropToRatio(1, 1);
					$image->resize(35, 35);
					$image->saveChanges();

					copy('./images/thumb_crop_' . $file, './images/130_q_' . $file);
					$image = new fImage('./images/130_q_' . $file);
					$image->cropToRatio(1, 1);
					$image->resize(130, 130);
					$image->saveChanges();

					@unlink('./tmp/tmp_' . $file);

					$this->model->addPhoto($user_id, $file);

					return '<html><head></head><body onload="parent.uploadPhotoReady(' . $user_id . ',\'./images/mini_q_' . $file . '\');"></body></html>';
				}
			}
		}
	}

	public function xhr_continueMail($data)
	{
		if ($this->func->isOrgaTeam() || $this->func->isBotschafter()) {
			$mail_id = (int)$data['id'];

			$mail = $this->model->getOne_send_email($mail_id);

			$bezirk = $this->model->getMailBezirk($this->func->getBezirkId());
			$bezirk['email'] = EMAIL_PUBLIC;
			$bezirk['email_name'] = EMAIL_PUBLIC_NAME;
			$recip = $this->model->getMailNext($mail_id);

			$mbmodel = new MailboxModel();
			$mailbox = $mbmodel->getMailbox((int)$mail['mailbox_id']);
			$mailbox['email'] = $mailbox['name'] . '@' . DEFAULT_HOST;

			$sender = $this->model->getValues(array('geschlecht', 'name'), 'foodsaver', $this->func->fsId());

			if (empty($recip)) {
				return json_encode(array('status' => 2, 'comment' => 'Es wurden alle E-Mails verschickt'));
				exit();
			}

			$this->model->setEmailStatus($mail['id'], $recip, 1);

			foreach ($recip as $fs) {
				$anrede = 'Liebe/r';
				if ($fs['geschlecht'] == 1) {
					$anrede = 'Lieber';
				} elseif ($fs['geschlecht'] == 2) {
					$anrede = 'Liebe';
				} else {
					$anrede = 'Liebe/r';
				}

				$search = array('{NAME}', '{ANREDE}', '{EMAIL}');
				$replace = array($fs['name'], $anrede, $fs['email']);

				$attach = false;
				if (!empty($mail['attach'])) {
					$attach = json_decode($mail['attach'], true);
				}

				$message = str_replace($search, $replace, $mail['message']);
				$subject = str_replace($search, $replace, $mail['name']);

				//$fs['email'] = 'kontakt@prographix.de';
				$check = false;
				if ($mail['mode'] == 2) {
					if ($this->func->libmail($mailbox, $fs['email'], $subject, $message, $attach, $fs['token'])) {
						$check = true;
					}
				} else {
					if ($this->model->add_message(array(
						'sender_id' => $this->func->fsId(),
						'recip_id' => $fs['id'],
						'unread' => 1,
						'name' => $subject,
						'msg' => $message,
						'time' => date('Y-m-d H:i:s'),
						'attach' => $mail['attach']
					))
					) {
						$this->func->tplMail(9, $fs['email'], array(
							'name' => $fs['name'],
							'sender' => $sender['name'],
							'anrede' => $this->func->genderWord($sender['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
							'link' => BASE_URL . '/?page=message&amp;conv=' . (int)$this->func->fsId()
						));
						$check = true;
					}
				}

				if (!$check) {
					$this->model->setEmailStatus($mail['id'], $fs['id'], 3);
				} else {
					$this->model->setEmailStatus($mail['id'], $fs['id'], 2);
				}
			}

			$mails_left = $this->model->getMailsLeft($mail['id']);
			if ($mails_left) {
				// throttle to 5 mails per second here to avoid queue bloat
				sleep(2);
			}

			return json_encode(array('left' => $mails_left, 'status' => 1, 'comment' => 'Versende E-Mails ... (aktuelle E-Mail-Adresse: ' . $fs['email'] . ')'));
		} else {
			return 0;
		}
	}

	public function xhr_uploadPhoto($data)
	{
		$func = '';

		if (isset($_POST['action']) && $_POST['action'] == 'upload') {
			$id = strip_tags($_POST['pic_id']);
			if (isset($_FILES['uploadpic'])) {
				$error = 0;
				$datei = $_FILES['uploadpic']['tmp_name'];
				$datein = $_FILES['uploadpic']['name'];
				$datein = strtolower($datein);
				$datein = str_replace('.jpeg', '.jpg', $datein);
				$dateiendung = strtolower(substr($datein, strlen($datein) - 4, 4));
				if ($this->func->is_allowed($_FILES['uploadpic'])) {
					try {
						$file = $this->func->makeUnique() . $dateiendung;
						move_uploaded_file($datei, './tmp/' . $file);
						$image = new fImage('./tmp/' . $file);
						$image->resize(550, 0);
						$image->saveChanges();
					} catch (Exception $e) {
						$func = 'parent.pic_error(\'Deine Datei schein nicht in Ordnung zu sein, nimm am besten ein normales jpg Bild\',\'' . $id . '\');';
					}

					$func = 'parent.fotoupload(\'' . $file . '\',\'' . $id . '\');';
				} else {
					$func = 'parent.pic_error(\'Deine Datei schein nicht in Ordnung zu sein, nimm am besten ein normales jpg Bild\',\'' . $id . '\');';
				}
			}
		} elseif (isset($_POST['action']) && $_POST['action'] == 'crop') {
			$file = str_replace('/', '', $_POST['file']);
			if ($img = $this->func->cropImage($file, $_POST['x'], $_POST['y'], $_POST['w'], $_POST['h'])) {
				$id = strip_tags($_POST['pic_id']);

				@unlink('images/' . $file);
				@unlink('images/crop_' . $file);
				@unlink('images/thumb_crop_' . $file);

				copy('tmp/' . $file, 'images/' . $file);
				copy('tmp/crop_' . $file, 'images/crop_' . $file);
				copy('tmp/thumb_crop_' . $file, 'images/thumb_crop_' . $file);

				@unlink('tmp/' . $file);
				@unlink('tmp/crop_' . $file);
				@unlink('tmp/thumb_crop_' . $file);

				$this->func->makeThumbs($file);

				$this->model->updatePhoto($this->func->fsId(), $file);

				$func = 'parent.picFinish(\'' . $img . '\',\'' . $id . '\');';
			} else {
				$func = 'alert(\'Es ist ein Fehler aufgetreten, Sorry, probiers nochmal\');';
			}
		}

		echo '<html>
	<head><title>Upload</title></head><body onload="' . $func . '"></body>
	</html>';
	}

	public function xhr_update_newbezirk($data)
	{
		if ($this->func->isOrgaTeam()) {
			$data['name'] = strip_tags($data['name']);
			$data['name'] = str_replace(array('/', '"', "'", '.', ';'), '', $data['name']);
			$data['has_children'] = 0;
			$data['email_pass'] = '';
			$data['email_name'] = 'Foodsharing ' . $data['name'];

			if (!empty($data['name'])) {
				if ($out = $this->model->add_bezirk($data)) {
					$this->model->update('UPDATE ' . PREFIX . 'bezirk SET has_children = 1 WHERE `id` = ' . $this->model->intval($data['parent_id']));

					return json_encode(array(
						'status' => 1,
						'script' => '$("#tree").dynatree("getTree").reload();pulseInfo("' . $data['name'] . ' wurde angelegt");'
					));
				}
			}
		}
	}

	public function xhr_update_abholen($data)
	{
		if ($this->model->isVerantwortlich($data['bid']) || $this->func->isBotschafter()) {
			$this->model->del('DELETE FROM 	`' . PREFIX . 'abholzeiten` WHERE `betrieb_id` = ' . $this->model->intval($data['bid']));

			if (is_array($data['newfetchtime'])) {
				for ($i = 0; $i < (count($data['newfetchtime']) - 1); ++$i) {
					$this->model->sql('
				REPLACE INTO 	`' . PREFIX . 'abholzeiten`
				(
						`betrieb_id`,
						`dow`,
						`time`,
						`fetcher`
				)
				VALUES
				(
					' . $this->model->intval($data['bid']) . ',
					' . $this->model->intval($data['newfetchtime'][$i]) . ',
					' . $this->model->strval($this->func->preZero($data['nfttime']['hour'][$i]) . ':' . $this->func->preZero($data['nfttime']['min'][$i]) . ':00') . ',
					' . $this->model->intval($data['nft-count'][$i]) . '
				)
			');
				}
			}
			$betrieb = $this->model->getVal('name', 'betrieb', $data['bid']);
			$model = new StoreModel();
			$model->addBell($data['team'], 'store_cr_times_title', 'store_cr_times', 'img img-store brown', array(
				'href' => '/?page=fsbetrieb&id=' . (int)$data['bid']
			), array(
				'user' => S::user('name'),
				'name' => $betrieb
			), 'store-time-' . (int)$data['bid']);

			return json_encode(array('status' => 1));
		}
	}

	public function xhr_update_bezirk($data)
	{
		return json_encode($this->model->update('
		UPDATE `' . PREFIX . 'bezirk`
		SET 	`email` = ' . $this->model->strval($data['email']) . ', 
				`email_pass` = ' . $this->model->strval($data['email_pass']) . ' 
			
				WHERE 	`id` = ' . $this->model->intval($data['bezirk_id']) . '
		'));
	}

	public function xhr_bezirkTree($data)
	{
		if ($bezirk = $this->model->getBezirkByParent($data['p'])) {
			$out = array();
			foreach ($bezirk as $b) {
				$hc = false;
				if ($b['has_children'] == 1) {
					$hc = true;
				}
				$out[] = array(
					'title' => $b['name'],
					'isLazy' => $hc,
					'isFolder' => $hc,
					'ident' => $b['id'],
					'type' => $b['type']
				);
			}
		} else {
			$out = array('status' => 0);
		}

		return json_encode($out);
	}

	public function xhr_bteamstatus($data)
	{
		$allow = array(
			0 => true,
			1 => true,
			2 => true
		);
		if ($this->model->isVerantwortlich($_GET['bid']) && isset($allow[(int)$_GET['s']])) {
			return $this->model->update('
			UPDATE `' . PREFIX . 'betrieb`
			SET 	`team_status` = ' . (int)$_GET['s'] . '
			WHERE 	`id` = ' . (int)$_GET['bid'] . '
		');
		}
	}

	public function xhr_getBezirk($data)
	{
		global $g_data;

		$g_data = $this->model->getOne_bezirk($data['id']);

		$g_data['mailbox_name'] = '';
		if ($mbname = $this->model->getMailboxname($g_data['mailbox_id'])) {
			$g_data['mailbox_name'] = $mbname;
		}

		$foodsaver_values = $this->model->getBasics_foodsaver($data['id']);

		$out = array();
		$out['status'] = 1;
		$id = $this->func->id('botschafter');

		$inputs = '<input type="text" name="' . $id . '[]" value="" class="tag input text value" />';
		if (!empty($g_data['foodsaver'])) {
			$inputs = '';
			if (isset($g_data['botschafter']) && is_array($g_data['botschafter'])) {
				foreach ($g_data['botschafter'] as $fs) {
					$inputs .= '<input type="text" name="' . $id . '[' . $fs['id'] . '-a]" value="' . $fs['name'] . '" class="tag input text value" />';
				}
			}
		}

		$inputs = '<div id="' . $id . '">' . $inputs . '</div>';

		$cats = $this->model->getBasics_bezirk();
		$out['html'] = $this->v_utils->v_form('bezirkForm', array(
				$this->v_utils->v_form_hidden('bezirk_id', (int)$data['id']),
				$this->v_utils->v_form_select('parent_id', array('values' => $cats)),
				$this->v_utils->v_form_select('master', array('label' => 'Master-Bezirk', 'desc' => 'Alle Foodsaver sind automatisch mit im Master-Bezirk, sofern einer angegeben wurde', 'values' => $cats)),
				$this->v_utils->v_form_text('name'),
				$this->v_utils->v_form_text('mailbox_name', ['desc' => 'Achtung! Nicht willkürlich ändern! Auch darauf achten, dass diese Adresse unter Mailboxen verwalten noch nicht existiert.']),
				$this->v_utils->v_form_text('email_name', array('label' => 'Absendername')),
				$this->v_utils->v_form_select('type', array('label' => 'Bezirkstyp', 'values' => array(
					array('id' => '1', 'name' => 'Stadt'),
					array('id' => '8', 'name' => 'Großstadt (ohne Anmeldemöglichkeit)'),
					array('id' => '9', 'name' => 'Stadtteil'),
					array('id' => '2', 'name' => 'Bezirk'),
					array('id' => '3', 'name' => 'Region'),
					array('id' => '5', 'name' => 'Bundesland'),
					array('id' => '6', 'name' => 'Land'),
					array('id' => '7', 'name' => 'Orgateam')
				))),
				$this->v_utils->v_input_wrapper($this->func->s($id), $inputs, $id)
			), array('submit' => $this->func->s('save'))) .
			$this->v_utils->v_input_wrapper('Master-Update', '<a class="button" href="#" class="button" onclick="if(confirm(\'Master-Update wirklich starten?\')){ajreq(\'masterupdate\',{app:\'geoclean\',id:' . (int)$data['id'] . '});}return false;">Master-Update starten</a>', 'masterupdate', array('desc' => 'Bei allen Kindbezirken ' . $g_data['name'] . ' als Master eintragen'));

		$out['script'] = '
		$("#bezirkform-form").unbind("submit");	
		$("#bezirkform-form").submit(function(ev){
			ev.preventDefault();
			
			$("#dialog-confirm-msg").html("Sicher, dass Du die &Auml;nderungen am Bezirk speichern m&ouml;chtest?");
			
			$( "#dialog-confirm" ).dialog("option","buttons",{
					"Ja, Speichern": function() 
					{
						showLoader();
						$.ajax({
							url: "xhr.php?f=saveBezirk",
							data: $("#bezirkform-form").serialize(),
							dataType: "json",
							success: function(data) {
								$("#info-msg").html("");
								$.globalEval(data.script);
								$( "#dialog-confirm" ).dialog( "close" );
								$("#tree").dynatree("getTree").reload();
							},
							complete: function(){
								hideLoader();
							}
						});
					},
					"Nein, doch nicht": function()
					{
						$( "#dialog-confirm" ).dialog( "close" );
					}
				});
			
			$("#dialog-confirm").dialog("open");
			
		});
			
		$("input[type=\'submit\']").button();
			
		$("#' . $id . ' input").tagedit({
			autocompleteURL: "xhr.php?f=getRecip",
			allowEdit: false,
			allowAdd: false
		});	
					
		$(window).keydown(function(event){
		    if(event.keyCode == 13) {
		      event.preventDefault();
		      return false;
		    }
		  });		
	';

		if ($foodsaver = $this->model->getFsMap($data['id'])) {
			$out['foodsaver'] = $foodsaver;
		}
		if ($betriebe = $this->model->getMapsBetriebe($data['id'])) {
			$out['betriebe'] = $betriebe;
			foreach ($out['betriebe'] as $i => $b) {
				$img = '';
				if ($b['kette_id'] != 0) {
					if ($img = $this->model->getVal('logo', 'kette', $b['kette_id'])) {
						$img = '<a href="/?page=betrieb&id=' . (int)$b['id'] . '"><img style="float:right;margin-left:10px;" src="' . $this->func->idimg($img, 100) . '" /></a>';
					}
				}
				$button = '';
				if ($this->model->isInTeam($b['id'])) {
					$button = '<div style="text-align:center;padding:top:8px;"><span onclick="goTo(\'/?page=fsbetrieb&id=' . (int)$b['id'] . '\');" class="bigbutton cardbutton ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><span class="ui-button-text">Zur Teamseite</span></span></div>';
				} else {
					$button = '<div style="text-align:center;padding:top:8px;"><span onclick="betriebRequest(' . (int)$b['id'] . ');" class="bigbutton cardbutton ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" role="button" aria-disabled="false"><div style="text-align:center;padding:top:8px;"><span class="ui-button-text">Ich möchte hier Lebensmittel abholen</span></span></div>';
				}

				$verantwortlicher = '';
				if ($v = $this->model->getTeamleader($b['id'])) {
					$verantwortlicher = '<p><a href="#" onclick="profile(' . (int)$b['id'] . ');return false;"><img src="' . $this->func->img() . '" /></a><a href="#" onclick="profile(' . (int)$b['id'] . ');return false;">' . $v['name'] . '</a> ist verantwortlich</p>';
				}

				$out['betriebe'][$i]['bubble'] = '<div style="height:110px;overflow:hidden;width:270px;"><div style="margin-right:5px;float:right;">' . $img . '</div><h1 style="font-size:13px;font-weight:bold;margin-bottom:8px;"><a onclick="betrieb(' . (int)$b['id'] . ');return false;" href="#">' . $this->func->jsSafe($b['name']) . '</a></h1><p>' . $this->func->jsSafe($b['str'] . ' ' . $b['hsnr']) . '</p><p>' . $this->func->jsSafe($b['plz']) . ' ' . $this->func->jsSafe($b['stadt']) . '</p>' . $button . '</div><div style="clear:both;"></div>';
			}
		}

		return json_encode($out);
	}

	public function xhr_acceptBezirkRequest($data)
	{
		if ($this->func->isBotFor($data['bid']) || $this->func->isOrgaTeam()) {
			$this->model->acceptBezirkRequest($data['fsid'], $data['bid']);

			return json_encode(array('status' => 1));
		}
	}

	public function xhr_denyBezirkRequest($data)
	{
		if ($this->func->isBotFor($data['bid']) || $this->func->isOrgaTeam()) {
			$this->model->denyBezirkRequest($data['fsid'], $data['bid']);

			return json_encode(array('status' => 1));
		}
	}

	public function xhr_denyRequest($data)
	{
		if ($this->func->fsId() == $data['fsid'] || $this->model->isVerantwortlich($data['bid'])) {
			$this->model->denyRequest($data['fsid'], $data['bid']);

			$msg = 'Deine Anfrage wurde erfolgreich zur&uuml;ckgezogen!';

			return json_encode(array('status' => 1, 'msg' => $msg));
		} else {
			$msg = 'Es ist ein Fehler aufgetreten!';

			return json_encode(array('status' => 0, 'msg' => $msg));
		}
	}

	public function xhr_acceptRequest($data)
	{
		if ($this->model->isVerantwortlich($data['bid']) || $this->func->isBotschafter()) {
			$this->model->acceptRequest($data['fsid'], $data['bid']);

			$this->model->add_betrieb_notiz(array(
				'foodsaver_id' => $data['fsid'],
				'betrieb_id' => $data['bid'],
				'text' => '{ACCEPT_REQUEST}',
				'zeit' => date('Y-m-d H:i:s'),
				'milestone' => 2
			));

			$bezirk_id = $this->model->getVal('bezirk_id', 'betrieb', $data['bid']);
			$this->model->linkBezirk($data['fsid'], $bezirk_id);

			return json_encode(array('status' => 1));
		}
	}

	public function xhr_warteRequest($data)
	{
		if ($this->model->isVerantwortlich($data['bid']) || $this->func->isBotschafter() || $this->func->isOrgaTeam()) {
			$this->model->warteRequest($data['fsid'], $data['bid']);

			return json_encode(array('status' => 1));
		}
	}

	public function xhr_betriebRequest($data)
	{
		$status = 1;
		$msg = 'Hallo Welt';
		$foodsaver = $this->model->getVal('name', 'foodsaver', $this->func->fsId());
		$betrieb = $this->model->getVal('name', 'betrieb', $data['id']);
		$bezirk_id = $this->model->getVal('bezirk_id', 'betrieb', $data['id']);
		if ($fsid = $this->model->getVerantwortlicher($data['id'])) {
			$msg = 'Der Verartwortliche wurde über Deine Anfrage informiert und wird sich bei Dir melden!';

			$biebs = $this->model->getBiebsForStore($data['id']);

			$model = new StoreModel();
			$model->addBell($biebs, 'store_new_request_title', 'store_new_request', 'img img-store brown', array(
				'href' => '/?page=fsbetrieb&id=' . (int)$data['id']
			), array(
				'user' => S::user('name'),
				'name' => $betrieb
			), 'store-request-' . (int)$data['id']);
		} else {
			$msg = 'Für Diesen Betrieb gibt es noch keinen Verantwortlichen, Der Botschafter wurde informiert';

			$botsch = array();
			$add = '';
			if ($b = $this->model->getBotschafter($bezirk_id)) {
				foreach ($b as $bb) {
					$botsch[] = $bb['id'];
				}
			} else {
				$botsch = $this->model->getOrgateam();
				$add = ' Es gibt aber keinen Botschafter';
			}

			$model = new StoreModel();
			$model->addBell($botsch, 'store_new_request_title', 'store_new_request', 'img img-store brown', array(
				'href' => '/?page=fsbetrieb&id=' . (int)$data['id']
			), array(
				'user' => S::user('name'),
				'name' => $betrieb
			), 'store-request-' . (int)$data['id']);
		}

		$this->model->teamRequest($this->func->fsId(), $data['id']);

		return json_encode(array('status' => $status, 'msg' => $msg));
	}

	public function xhr_saveBezirk($data)
	{
		global $g_data;
		$g_data = $data;

		$mbid = (int)$this->model->qOne('SELECT mailbox_id FROM ' . PREFIX . 'bezirk WHERE id = ' . (int)$data['bezirk_id']);

		if (strlen($g_data['mailbox_name']) > 1) {
			if ($mbid > 0) {
				$this->model->update('UPDATE ' . PREFIX . 'mailbox SET name = ' . $this->model->strval($g_data['mailbox_name']) . ' WHERE id = ' . (int)$mbid);
			} else {
				$mbid = $this->model->insert('INSERT INTO ' . PREFIX . 'mailbox(`name`)VALUES(' . $this->model->strval($g_data['mailbox_name']) . ')');
				$this->model->update('UPDATE ' . PREFIX . 'bezirk SET mailbox_id = ' . (int)$mbid . ' WHERE id = ' . (int)$data['bezirk_id']);
			}
		}

		$botschafter = $this->func->handleTagselect('botschafter');

		$this->model->update_bezirkNew($data['bezirk_id'], $g_data);

		return $this->xhr_out('', 'pulseInfo("' . $this->func->s('edit_success') . '");');
	}

	public function xhr_addFetcher($data)
	{
		if (($this->model->isInTeam($data['bid']) || $this->func->isBotschafter() || $this->func->isOrgaTeam()) && $this->func->isVerified()) {
			/*
			 * 	[f] => addFetcher
				[date] => 2013-09-23 20:00:00
				[bid] => 1
			 */

			if (!empty($data['to'])) {
				$this->func->incLang('fsbetrieb');
				if (empty($data['from'])) {
					$data['from'] = date('Y-m-d');
				}
				$time = explode(' ', $data['date']);
				$time = $time[1];

				$from = strtotime($data['from']);
				$to = strtotime($data['to']);
				if ($to > time() + 86400 * 7 * 3) {
					$this->func->info('Das Datum liegt zu weit in der Zukunft!');

					return 0;
				}

				$start = strtotime($data['date']);

				$cur_date = $from;

				$dow = date('w', $start);
				$count = 0;
				do {
					if (date('w', $cur_date) == $dow) {
						++$count;
						$this->model->addFetcher($this->func->fsId(), $data['bid'], date('Y-m-d', $cur_date) . ' ' . $time);
					}
					if ($count > 20) {
						break;
					}
					// + 1 Tag
					$cur_date += 86400;
				} while ($to > $cur_date);
				$this->func->info($this->func->s('date_add_successful'));

				return '2';
			} elseif (!empty($data['from'])) {
				return 0;
			} else {
				$data['date'] = date('Y-m-d H:i:s', strtotime($data['date']));
				if ($this->model->addFetcher($this->func->fsId(), $data['bid'], $data['date'])) {
					return $this->func->img($this->model->getVal('photo', 'foodsaver', $this->func->fsId()));
				}
			}
		}

		return '0';
	}

	public function xhr_delDate($data)
	{
		$status = 0;
		$betriebModel = new StoreModel();
		if ($betriebModel->isInTeam($data['bid']) && isset($data['date'])) {
			if ($betriebModel->deleteFetchDate($this->func->fsId(), $data['bid'], $data['date'])) {
				$status = 1;
			}

			if (isset($data['msg'])) {
				$betriebModel->addTeamMessage($data['bid'], $data['msg']);
			}
		}

		return json_encode(array(
			'status' => $status
		));
	}

	public function xhr_fetchDeny($data)
	{
		$betriebModel = new StoreModel();
		if ($betriebModel->isVerantwortlich($data['bid']) || $this->func->isOrgaTeam() && isset($data['date'])) {
			$betriebModel->deleteFetchDate($data['fsid'], $data['bid'], date('Y-m-d H:i:s', strtotime($data['date'])));

			return 1;
		}
	}

	public function xhr_fetchConfirm($data)
	{
		if ($this->model->isVerantwortlich($data['bid']) || $this->func->isOrgaTeam()) {
			$this->model->confirmFetcher($data['fsid'], $data['bid'], date('Y-m-d H:i:s', strtotime($data['date'])));

			return 1;
		}
	}

	/**
	 * @param $data
	 *
	 * @return string
	 */
	public function xhr_becomeBezirk($data)
	{
		if ($this->func->may()) {
			Mem::delPageCache('/?page=dashboard');
			$bezirk_id = (int)$data['b'];
			$new = '';
			if (isset($data['new'])) {
				$new = preg_replace('/a-zA-ZäöüÄÖÜß\ /', '', $data['new']);
			}

			if (empty($new) && $bezirk_id > 0) {
				/*
				if(($active = $db->qOne('SELECT `active` FROM `'.PREFIX.'foodsaver_has_bezirk` WHERE `bezirk_id` = '.(int)$bezirk_id.' AND `foodsaver_id` = '.(int)$this->func->fsId().' ')) !== false)
				{
					if($active == 1)
					{
						return json_encode(array(
								'script' => 'pulseInfo(\''.$this->func->jsSafe($this->func->s('already_in_bezirk')).'\');',
								'status' => 2
						));
					}
				}
				*/
				/*
					// schon im bezirk
					if($active == 1)
					{
						return json_encode(array(
							'script' => 'pulseInfo(\''.$this->func->jsSafe($this->func->s('already_in_bezirk')).'\');',
							'status' => 2
						));
					}
					else
					{
						return json_encode(array(
							'script' => 'pulseInfo(\''.$this->func->jsSafe($this->func->s('request_already_send')).'\');',
							'status' => 2
						));
					}
				}
				else
				{
				*/
				$active = 1;
				$this->model->insert('
					REPLACE INTO  `' . PREFIX . 'foodsaver_has_bezirk` (`bezirk_id`,`foodsaver_id`,`active`)
					VALUES (' . (int)$bezirk_id . ',' . $this->func->fsId() . ', ' . $active . ' )
				');

				if (!$this->func->getBezirkId()) {
					$this->model->update('UPDATE ' . PREFIX . 'foodsaver SET bezirk_id = ' . (int)$bezirk_id . ' WHERE id = ' . (int)$this->func->fsId());
				}

				if ($bots = $this->model->getBotschafter($bezirk_id)) {
					$model = new Model();

					if (
						($foodsaver = $this->model->getValues(array('verified', 'name', 'nachname', 'photo'), 'foodsaver', $this->func->fsId())) &&
						($bezirk = $this->model->getValues(array('name'), 'bezirk', $bezirk_id))
					) {
						if ($foodsaver['verified'] == 1) {
							$model->addBell(
								$bots,
								'new_foodsaver_title',
								'new_foodsaver_verified',
								$this->func->img($foodsaver['photo'], 50),
								array('href' => '#', 'onclick' => 'profile(' . (int)$this->func->fsId() . ');return false;'),
								array(
									'name' => $foodsaver['name'] . ' ' . $foodsaver['nachname'],
									'bezirk' => $bezirk['name']
								),
								'new-fs-' . $this->func->fsId()
							);
						} else {
							$model->addBell(
								$bots,
								'new_foodsaver_title',
								'new_foodsaver',
								$this->func->img($foodsaver['photo'], 50),
								array('href' => '#', 'onclick' => 'profile(' . (int)$this->func->fsId() . ');return false;'),
								array(
									'name' => $foodsaver['name'] . ' ' . $foodsaver['nachname'],
									'bezirk' => $bezirk['name']
								),
								'new-fs-' . $this->func->fsId(),
								false
							);
						}
					}
				}

				if ($botschafter = $this->model->getBotschafter($bezirk_id)) {
					return json_encode(array(
						'active' => $active,
						'status' => 1,
						'botschafter' => $botschafter
					));
				} else {
					return json_encode(array(
						'active' => $active,
						'status' => 1,
						'botschafter' => false
					));
				}
				//}
			}
		}
	}

	public function xhr_delBPost($data)
	{
		$fsid = $this->model->getVal('foodsaver_id', 'betrieb_notiz', $data['pid']);
		if ($this->func->isOrgaTeam() || $fsid == $this->func->fsId()) {
			$this->model->deleteBPost($data['pid']);

			return 1;
		} else {
			return 0;
		}
	}

	public function xhr_delPost($data)
	{
		$regionModel = new RegionModel();

		$fsid = $this->model->getVal('foodsaver_id', 'theme_post', $data['pid']);
		$bezirkId = $regionModel->getBezirkForPost($data['pid']);
		$bezirkType = $regionModel->getBezirkType($bezirkId);

		if ($this->func->isOrgaTeam() || $fsid == $this->func->fsId() || ($this->func->isBotFor($bezirkId) && $bezirkType == 7)) {
			$regionModel->deletePost($data['pid']);

			return 1;
		} else {
			return 0;
		}
	}

	public function xhr_abortEmail($data)
	{
		if ($this->func->fsId() == $this->model->getVal('foodsaver_id', 'send_email', $data['id'])) {
			$this->model->update('UPDATE ' . PREFIX . 'email_status SET status = 4 WHERE email_id = ' . (int)$data['id']);
		}
	}

	public function xhr_bcontext($data)
	{
		if ($this->model->isVerantwortlich($data['bid']) || $this->func->isBotFor($data['bzid']) || $this->func->isOrgaTeam()) {
			$check = false;
			if ($data['action'] == 'toteam') {
				$check = true;
				$this->model->update('UPDATE `' . PREFIX . 'betrieb_team` SET `active` = 1 WHERE foodsaver_id = ' . (int)$data['fsid'] . ' AND betrieb_id = ' . (int)$data['bid']);
			} elseif ($data['action'] == 'tojumper') {
				$check = true;
				$this->model->update('UPDATE `' . PREFIX . 'betrieb_team` SET `active` = 2 WHERE foodsaver_id = ' . (int)$data['fsid'] . ' AND betrieb_id = ' . (int)$data['bid']);
			} elseif ($data['action'] == 'delete') {
				$check = true;
				$this->model->del('DELETE FROM `' . PREFIX . 'betrieb_team` WHERE foodsaver_id = ' . (int)$data['fsid'] . ' AND betrieb_id = ' . (int)$data['bid']);
				$this->model->del('DELETE FROM `' . PREFIX . 'abholer` WHERE `betrieb_id` = ' . (int)$data['bid'] . ' AND `foodsaver_id` = ' . (int)$data['fsid'] . ' AND `date` > NOW()');

				$msg = new MessageModel();

				if ($tcid = $msg->getBetriebConversation((int)$data['bid'])) {
					$msg->deleteUserFromConversation($tcid, (int)$data['fsid'], true);
				}
				if ($scid = $msg->getBetriebConversation((int)$data['bid'], true)) {
					$msg->deleteUserFromConversation($scid, (int)$data['fsid'], true);
				}
			}

			if ($check) {
				return json_encode(array(
					'status' => 1
				));
			}
		}
	}
}
