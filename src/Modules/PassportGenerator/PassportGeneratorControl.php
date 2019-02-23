<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Endroid\QrCode\QrCode;
use setasign\Fpdi\Tcpdf\Fpdi;

final class PassportGeneratorControl extends Control
{
	private $regionId;
	private $region;
	private $bellGateway;
	private $regionGateway;
	private $passportGeneratorGateway;
	private $foodsaverGateway;

	public function __construct(
		PassportGeneratorView $view,
		BellGateway $bellGateway,
		RegionGateway $regionGateway,
		PassportGeneratorGateway $passportGateway,
		FoodsaverGateway $foodsaverGateway
	) {
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->regionGateway = $regionGateway;
		$this->passportGeneratorGateway = $passportGateway;
		$this->foodsaverGateway = $foodsaverGateway;

		parent::__construct();

		$this->regionId = false;
		if (($this->regionId = $this->func->getGetId('bid')) === false) {
			$this->regionId = $this->session->getCurrentBezirkId();
		}

		// isBotForA() returns true if user is an ambassador (AMB) for this region. If the user is an AMB and the bezirk/region is a working group it returns false
		if ($this->session->isBotForA([$this->regionId], false, true) || $this->session->isOrgaTeam()) {
			$this->region = false;
			if ($region = $this->regionGateway->getBezirk($this->regionId)) {
				$this->region = $region;
			}
		} else {
			$this->linkingHelper->go('/?page=dashboard');
		}
	}

	public function index(): void
	{
		$this->pageCompositionHelper->addBread($this->region['name'], '/?page=bezirk&bid=' . $this->regionId . '&sub=forum');
		$this->pageCompositionHelper->addBread('Pass-Generator', $this->linkingHelper->getSelf());

		$this->pageCompositionHelper->addTitle($this->region['name']);
		$this->pageCompositionHelper->addTitle('Pass Generator');

		if (isset($_POST['foods']) && !empty($_POST['foods'])) {
			$this->generate($_POST['foods']);
		}

		if ($regions = $this->passportGeneratorGateway->getPassFoodsaver($this->regionId)) {
			$this->pageCompositionHelper->addHidden('
			<div id="verifyconfirm-dialog" title="' . $this->func->s('verify_confirm_title') . '">
				' . $this->v_utils->v_info('<p>' . $this->func->s('verify_confirm') . '</p>', $this->func->s('verify_confirm_title')) . '
				<span class="button_confirm" style="display:none">' . $this->func->s('verify_confirm_button') . '</span>
				<span class="button_abort" style="display:none">' . $this->func->s('abort') . '</span>
			</div>');

			$this->pageCompositionHelper->addHidden('
			<div id="unverifyconfirm-dialog" title="Es ist ein Problem aufgetreten">
				' . $this->v_utils->v_info('<p>' . $this->func->s('unverify_confirm') . '</p>', $this->func->s('unverify_confirm_title')) . '
				<span class="button_confirm" style="display:none">' . $this->func->s('unverify_confirm_button') . '</span>
				<span class="button_abort" style="display:none">' . $this->func->s('abort') . '</span>
			</div>');

			$this->pageCompositionHelper->addContent('<form id="generate" method="post">');
			foreach ($regions as $region) {
				$this->pageCompositionHelper->addContent($this->view->passTable($region));
			}
			$this->pageCompositionHelper->addContent('</form>');
			$this->pageCompositionHelper->addContent($this->view->menubar(), CNT_RIGHT);
			$this->pageCompositionHelper->addContent($this->view->start(), CNT_RIGHT);
			$this->pageCompositionHelper->addContent($this->view->tips(), CNT_RIGHT);
		}

		if (isset($_GET['dl1'])) {
			$this->download1();
		}
		if (isset($_GET['dl2'])) {
			$this->download2();
		}
	}

	public function generate(array $foodsavers): void
	{
		$tmp = array();
		foreach ($foodsavers as $foodsaver) {
			$tmp[$foodsaver] = (int)$foodsaver;
		}
		$foodsavers = $tmp;
		$is_generated = array();

		$pdf = new Fpdi();
		$pdf->AddPage();
		$pdf->SetTextColor(0, 0, 0);
		$pdf->AddFont('Ubuntu-L', '', 'lib/font/ubuntul.php', true);
		$pdf->AddFont('AcmeFont Regular', '', 'lib/font/acmefont.php', true);

		$x = 0;
		$y = 0;
		$card = 0;

		$noPhoto = array();

		end($foodsavers);

		$pdf->setSourceFile('img/foodsharing_logo.pdf');
		$fs_logo = $pdf->importPage(1);

		foreach ($foodsavers as $fs_id) {
			if ($foodsaver = $this->foodsaverGateway->getFoodsaverDetails($fs_id)) {
				if (empty($foodsaver['photo'])) {
					$noPhoto[] = $foodsaver['name'] . ' ' . $foodsaver['nachname'];

					$this->bellGateway->addBell(
						$foodsaver['id'],
						'passgen_failed_title',
						'passgen_failed',
						'fas fa-camera',
						['href' => '/?page=settings'],
						['user' => $this->session->user('name')],
						'pass-fail-' . $foodsaver['id']
					);
					//continue;
				}

				$pdf->SetTextColor(0, 0, 0);

				++$card;

				$this->passportGeneratorGateway->passGen($this->session->id(), $foodsaver['id']);

				$pdf->Image('img/pass_bg.png', 10 + $x, 10 + $y, 83, 55);

				$pdf->SetFont('Ubuntu-L', '', 10);
				$name = $foodsaver['name'] . ' ' . $foodsaver['nachname'];
				$maxWidth = 49;
				if ($pdf->GetStringWidth($name) > $maxWidth) {
					$pdf->SetFont('Ubuntu-L', '', 8);
					if ($pdf->GetStringWidth($name) <= $maxWidth) {
						$pdf->Text(41 + $x, 30 + $y, $name);
					}
					$size = 8;
					while ($pdf->GetStringWidth($foodsaver['name']) > $maxWidth || $pdf->GetStringWidth($foodsaver['nachname']) > $maxWidth) {
						$size -= 0.5;
						$pdf->SetFont('Ubuntu-L', '', $size);
					}
					$pdf->Text(41 + $x, 30.2 + $y, $foodsaver['name']);
					$pdf->Text(41 + $x, 33.2 + $y, $foodsaver['nachname']);
				} else {
					$pdf->Text(41 + $x, 30 + $y, $name);
				}
				$pdf->SetFont('Ubuntu-L', '', 10);
				$pdf->Text(41 + $x, 39 + $y, $this->getRole($foodsaver['geschlecht'], $foodsaver['rolle']));
				$pdf->Text(41 + $x, 48 + $y, date('d. m. Y', time() - 1814400));
				$pdf->Text(41 + $x, 57 + $y, date('d. m. Y', time() + 94608000));

				$pdf->SetFont('Ubuntu-L', '', 6);
				$pdf->Text(41 + $x, 28 + $y, 'Name');
				$pdf->Text(41 + $x, 37 + $y, 'Rolle');
				$pdf->Text(41 + $x, 46 + $y, 'Gültig ab');
				$pdf->Text(41 + $x, 55 + $y, 'Gültig bis');

				$pdf->SetFont('Ubuntu-L', '', 9);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->SetXY(40 + $x, 13.2 + $y);
				$pdf->Cell(50, 5, 'ID ' . $fs_id, 0, 0, 'R');

				$pdf->SetFont('AcmeFont Regular', '', 5.3);
				$pdf->Text(12.8 + $x, 18.6 + $y, 'Teile Lebensmittel, anstatt sie wegzuwerfen!');

				$pdf->useTemplate($fs_logo, 13.5 + $x, 13.6 + $y, 29.8);

				$style = array(
					'vpadding' => 'auto',
					'hpadding' => 'auto',
					'fgcolor' => array(0, 0, 0),
					'bgcolor' => false, //array(255,255,255)
					'module_width' => 1, // width of a single module in points
					'module_height' => 1 // height of a single module in points
				);

				// QRCODE,L : QR-CODE Low error correction
				$pdf->write2DBarcode('https://foodsharing.de/profile/' . $fs_id, 'QRCODE,L', 70.5 + $x, 43 + $y, 20, 20, $style, 'N');

				if ($photo = $this->foodsaverGateway->getPhoto($fs_id)) {
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

				$is_generated[] = $foodsaver['id'];
			}
		}
		if (!empty($noPhoto)) {
			$last = array_pop($noPhoto);
			$this->func->info(implode(', ', $noPhoto) . ' und ' . $last . ' haben noch kein Foto hochgeladen und ihr Ausweis konnte nicht erstellt werden');
		}

		$this->passportGeneratorGateway->updateLastGen($is_generated);

		$bez = strtolower($this->region['name']);

		$bez = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);

		$pdf->Output('foodsaver_pass_' . $bez . '.pdf', 'D');
		exit();
	}

	public function getRole(int $gender_id, int $role_id)
	{
		$role = [
			0 => [ // not defined
				0 => 'Freiwillige_r',
				1 => 'Foodsaver_in',
				2 => 'Betriebsverantwortliche_r',
				3 => 'Botschafter_in',
				4 => 'Botschafter_in' // role 4 stands for Orga but is referred to an AMB for the business card
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

	private function download1(): void
	{
		$this->pageCompositionHelper->addJs('
			setTimeout(function(){goTo("/?page=passgen&bid=' . $this->regionId . '&dl2")},100);		
		');
	}

	private function download2(): void
	{
		$bez = strtolower($this->region['name']);

		$bez = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $bez);
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
