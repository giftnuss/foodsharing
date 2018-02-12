<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\Control;
use FPDI;

class PassportGeneratorControl extends Control
{
	private $bezirk_id;
	private $bezirk;

	public function __construct()
	{
		$this->model = new PassportGeneratorModel();
		$this->view = new PassportGeneratorView();

		parent::__construct();

		$this->bezirk_id = false;
		if (($this->bezirk_id = $this->func->getGetId('bid')) === false) {
			$this->bezirk_id = $this->func->getBezirkId();
		}

		if ($this->func->isBotFor($this->bezirk_id) || $this->func->isOrgaTeam()) {
			$this->bezirk = false;
			if ($bezirk = $this->model->getBezirk($this->bezirk_id)) {
				$this->bezirk = $bezirk;
			}
		} else {
			$this->func->go('/?page=dashboard');
		}
	}

	public function index()
	{
		$this->func->addBread($this->bezirk['name'], '/?page=bezirk&bid=' . $this->bezirk_id . '&sub=forum');
		$this->func->addBread('Pass-Generator', $this->func->getSelf());

		$this->func->addTitle($this->bezirk['name']);
		$this->func->addTitle('Pass Generator');

		if (isset($_POST['foods']) && !empty($_POST['foods'])) {
			$this->generate($_POST['foods']);
		}

		if ($bezirke = $this->model->getPassFoodsaver($this->bezirk_id)) {
			$this->func->addHidden('
			<div id="verifyconfirm-dialog" title="' . $this->func->s('verify_confirm_title') . '">
				' . v_info('<p>' . $this->func->s('verify_confirm') . '</p>', $this->func->s('verify_confirm_title')) . '
				<span class="button_confirm" style="display:none">' . $this->func->s('verify_confirm_button') . '</span>
				<span class="button_abort" style="display:none">' . $this->func->s('abort') . '</span>
			</div>');

			$this->func->addHidden('
			<div id="unverifyconfirm-dialog" title="Es ist ein Problem aufgetreten">
				' . v_info('<p>' . $this->func->s('unverify_confirm') . '</p>', $this->func->s('unverify_confirm_title')) . '
				<span class="button_confirm" style="display:none">' . $this->func->s('unverify_confirm_button') . '</span>
				<span class="button_abort" style="display:none">' . $this->func->s('abort') . '</span>
			</div>');

			$this->func->addContent('<form id="generate" method="post">');
			foreach ($bezirke as $b) {
				$this->func->addContent($this->view->passTable($b));
			}
			$this->func->addContent('</form>');
			$this->func->addContent($this->view->menubar(), CNT_RIGHT);
			$this->func->addContent($this->view->start(), CNT_RIGHT);
			$this->func->addContent($this->view->tips(), CNT_RIGHT);
		}

		if (isset($_GET['dl1'])) {
			$this->download1();
		}
		if (isset($_GET['dl2'])) {
			$this->download2();
		}
	}

	public function generate($foodsaver)
	{
		$tmp = array();
		foreach ($foodsaver as $fs) {
			$tmp[$fs] = (int)$fs;
		}
		$foodsaver = $tmp;
		$is_generated = array();

		include 'lib/phpqrcode/qrlib.php';

		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->SetTextColor(0, 0, 0);
		$pdf->AddFont('Ubuntu-L', '', 'Ubuntu-L.php');
		$pdf->AddFont('AcmeFont Regular', '', 'acmefontregular.php');

		$x = 0;
		$y = 0;
		$card = 0;

		$left = 0;
		$nophoto = array();

		end($foodsaver);

		$pdf->setSourceFile('img/foodsharing_logo.pdf');
		$fs_logo = $pdf->importPage(1);

		foreach ($foodsaver as $i => $fs_id) {
			if ($fs = $this->model->qRow('SELECT `photo`,`id`,`name`,`nachname`,`geschlecht`,`rolle` FROM ' . PREFIX . 'foodsaver WHERE `id` = ' . (int)$fs_id . ' ')) {
				if (empty($fs['photo'])) {
					$nophoto[] = $fs['name'] . ' ' . $fs['nachname'];

					$this->model->addBell(
						$fs['id'],
						'passgen_failed_title',
						'passgen_failed',
						'fa fa-camera',
						array('href' => '/?page=settings'),
						array('user' => S::user('name')),
						'pass-fail-' . $fs['id']
					);
					continue;
				}

				$pdf->SetTextColor(0, 0, 0);
				$pdf->AddFont('Ubuntu-L', '', 'Ubuntu-L.php');

				@unlink('tmp/qr_' . $fs_id . '.png');
				\QRcode::png($fs_id, 'tmp/qr_' . $fs_id . '.png', QR_ECLEVEL_L, 3.4, 0);

				++$card;

				$this->model->passGen($fs['id']);

				$pdf->Image('img/pass_bg.png', 10 + $x, 10 + $y, 83, 55);

				$pdf->SetFont('Ubuntu-L', '', 10);

				$pdf->Text(41.8 + $x, 34.4 + $y, utf8_decode($fs['name'] . ' ' . $fs['nachname']));
				$pdf->Text(41.8 + $x, 42.1 + $y, utf8_decode($this->getRole($fs['geschlecht'], $fs['rolle'])));
				$pdf->Text(41.8 + $x, 49.8 + $y, utf8_decode(date('d. m. Y', time() - 1814400)));
				$pdf->Text(41.8 + $x, 57.3 + $y, utf8_decode(date('d. m. Y', time() + 94608000)));

				$pdf->SetFont('Ubuntu-L', '', 6);
				$pdf->Text(41.8 + $x, 31.2 + $y, utf8_decode('Name'));
				$pdf->Text(41.8 + $x, 38.9 + $y, utf8_decode('Rolle'));
				$pdf->Text(41.8 + $x, 46.6 + $y, utf8_decode('Gültig ab'));
				$pdf->Text(41.8 + $x, 54.3 + $y, utf8_decode('Gültig bis'));

				$pdf->SetFont('Ubuntu-L', '', 9);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->SetXY(40 + $x, 13.2 + $y);
				$pdf->Cell(50, 5, 'ID ' . $fs_id, 0, 0, 'R');

				$pdf->SetFont('AcmeFont Regular', '', 5.3);
				$pdf->Text(13.9 + $x, 20.6 + $y, 'Teile Lebensmittel, anstatt sie wegzuwerfen!');

				$pdf->useTemplate($fs_logo, 13.5 + $x, 13.6 + $y, 29.8);

				$pdf->Image('tmp/qr_' . $fs_id . '.png', 70 + $x, 42.1 + $y);

				if ($photo = $this->model->getPhoto($fs_id)) {
					if (file_exists('images/crop_' . $photo)) {
						$pdf->Image('images/crop_' . $photo, 14 + $x, 29.7 + $y, 24);
					} elseif (file_exists('images/' . $photo)) {
						$pdf->Image('images/' . $photo, 14 + $x, 29.7 + $y, 22);
					}
				}

				if ($x == 0) {
					$x += 95;
				} else {
					$y += 65;
					$x = 0;
				}

				if ($card == 8) {
					$card = 0;
					$pdf->AddPage();
					$x = 0;
					$y = 0;
				}

				$is_generated[] = $fs['id'];
			}
		}
		if (!empty($nophoto)) {
			$last = array_pop($nophoto);
			$this->func->info(implode(', ', $nophoto) . ' und ' . $last . ' haben noch kein Foto hochgeladen und ihr Ausweis konnte nicht erstellt werden');
		}

		$this->model->updateLastGen($is_generated);

		$bez = strtolower($this->bezirk['name']);

		$bez = str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);

		$pdf->Output('D', 'foodsaver_pass_' . $bez . '.pdf');
		exit();
	}

	public function getRole($gender_id, $role_id)
	{
		$role = [
			0 => [ // not defined
				0 => 'Freiwillige/r',
				1 => 'Foodsaver',
				2 => 'Betriebsverantwortliche/r',
				3 => 'Botschafter/in',
				4 => 'Botschafter/in' // role 4 stands for Orga but is referred to an AMB for the business card
			],
			1 => [ // male
				0 => 'Freiwilliger',
				1 => 'Foodsaver',
				2 => 'Betriebsverantwortlicher',
				3 => 'Botschafter',
				4 => 'Botschafter'
			],
			2 => [ // female
				0 => 'Freiwillige',
				1 => 'Foodsaverin',
				2 => 'Betriebsverantwortliche',
				3 => 'Botschafterin',
				4 => 'Botschafterin'
			]
		];

		return $role[$gender_id][$role_id];
	}

	private function download1()
	{
		$this->func->addJs('
			setTimeout(function(){goTo("/?page=passgen&bid=' . $this->bezirk_id . '&dl2")},100);		
		');
	}

	private function download2()
	{
		$bez = strtolower($this->bezirk['name']);

		$bez = str_replace(array('ä', 'ö', 'ü', 'ß'), array('ae', 'oe', 'ue', 'ss'), $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);
		$file = 'data/pass/foodsaver_pass_' . $bez . '.pdf';

		$Dateiname = basename($file);
		$size = filesize($file);
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename=' . $Dateiname . '');
		header("Content-Length: $size");
		readfile($file);

		exit();
	}
}
