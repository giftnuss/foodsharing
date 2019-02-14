<?php

namespace Foodsharing\Modules\PassportGenerator;

use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use setasign\Fpdi;

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

		if ($this->session->isAdminFor($this->regionId) || $this->session->isOrgaTeam()) {
			$this->region = false;
			if ($region = $this->regionGateway->getBezirk($this->regionId)) {
				$this->region = $region;
			}
		} else {
			$this->func->go('/?page=dashboard');
		}
	}

	public function index(): void
	{
		$this->func->addBread($this->region['name'], '/?page=bezirk&bid=' . $this->regionId . '&sub=forum');
		$this->func->addBread('Pass-Generator', $this->func->getSelf());

		$this->func->addTitle($this->region['name']);
		$this->func->addTitle('Pass Generator');

		if (isset($_POST['foods']) && !empty($_POST['foods'])) {
			$this->generate($_POST['foods']);
		}

		if ($regions = $this->passportGeneratorGateway->getPassFoodsaver($this->regionId)) {
			$this->func->addHidden('
			<div id="verifyconfirm-dialog" title="' . $this->func->s('verify_confirm_title') . '">
				' . $this->v_utils->v_info('<p>' . $this->func->s('verify_confirm') . '</p>', $this->func->s('verify_confirm_title')) . '
				<span class="button_confirm" style="display:none">' . $this->func->s('verify_confirm_button') . '</span>
				<span class="button_abort" style="display:none">' . $this->func->s('abort') . '</span>
			</div>');

			$this->func->addHidden('
			<div id="unverifyconfirm-dialog" title="Es ist ein Problem aufgetreten">
				' . $this->v_utils->v_info('<p>' . $this->func->s('unverify_confirm') . '</p>', $this->func->s('unverify_confirm_title')) . '
				<span class="button_confirm" style="display:none">' . $this->func->s('unverify_confirm_button') . '</span>
				<span class="button_abort" style="display:none">' . $this->func->s('abort') . '</span>
			</div>');

			$this->func->addContent('<form id="generate" method="post">');
			foreach ($regions as $region) {
				$this->func->addContent($this->view->passTable($region));
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

	public function generate(array $foodsavers): void
	{
		$tmp = array();
		foreach ($foodsavers as $foodsaver) {
			$tmp[$foodsaver] = (int)$foodsaver;
		}
		$foodsavers = $tmp;
		$is_generated = array();

		$pdf = new Fpdi\Fpdi();
		$pdf->AddPage();
		$pdf->SetTextColor(0, 0, 0);
		$pdf->AddFont('Ubuntu-L', '', 'Ubuntu-L.php');
		$pdf->AddFont('AcmeFont Regular', '', 'acmefontregular.php');

		$x = 0;
		$y = 0;
		$card = 0;

		$noPhoto = array();

		end($foodsavers);

		$pdf->setSourceFile('img/foodsharing_logo.pdf');
		$fs_logo = $pdf->importPage(1);

		foreach ($foodsavers as $fs_id) {
			if ($foodsaver = $this->passportGeneratorGateway->fetchFoodsaverData($fs_id)) {
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
					continue;
				}

				$pdf->SetTextColor(0, 0, 0);
				$pdf->AddFont('Ubuntu-L', '', 'Ubuntu-L.php');

				++$card;

				$this->passportGeneratorGateway->passGen($this->session->id(), $foodsaver['id']);

				$pdf->Image('img/pass_bg.png', 10 + $x, 10 + $y, 83, 55);

				$pdf->SetFont('Ubuntu-L', '', 10);

				$pdf->Text(41.8 + $x, 34.4 + $y, utf8_decode($foodsaver['name'] . ' ' . $foodsaver['nachname']));
				$pdf->Text(41.8 + $x, 42.1 + $y, utf8_decode($this->getRole($foodsaver['geschlecht'], $foodsaver['rolle'])));
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

		$pdf->Output('D', 'foodsaver_pass_' . $bez . '.pdf');
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
		$this->func->addJs('
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
