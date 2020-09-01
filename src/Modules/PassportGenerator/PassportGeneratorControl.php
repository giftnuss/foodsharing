<?php

namespace Foodsharing\Modules\PassportGenerator;

use Endroid\QrCode\QrCode;
use Foodsharing\Modules\Bell\BellGateway;
use Foodsharing\Modules\Bell\DTO\Bell;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Gender;
use Foodsharing\Modules\Core\DBConstants\Foodsaver\Role;
use Foodsharing\Modules\Foodsaver\FoodsaverGateway;
use Foodsharing\Modules\Region\RegionGateway;
use Foodsharing\Utility\IdentificationHelper;
use setasign\Fpdi\Tcpdf\Fpdi;

final class PassportGeneratorControl extends Control
{
	private $regionId;
	private $region;
	private BellGateway $bellGateway;
	private RegionGateway $regionGateway;
	private PassportGeneratorGateway $passportGeneratorGateway;
	private FoodsaverGateway $foodsaverGateway;
	private IdentificationHelper $identificationHelper;

	public function __construct(
		PassportGeneratorView $view,
		BellGateway $bellGateway,
		RegionGateway $regionGateway,
		PassportGeneratorGateway $passportGateway,
		FoodsaverGateway $foodsaverGateway,
		IdentificationHelper $identificationHelper
	) {
		$this->view = $view;
		$this->bellGateway = $bellGateway;
		$this->regionGateway = $regionGateway;
		$this->passportGeneratorGateway = $passportGateway;
		$this->foodsaverGateway = $foodsaverGateway;
		$this->identificationHelper = $identificationHelper;

		parent::__construct();

		$this->regionId = false;
		if (($this->regionId = $this->identificationHelper->getGetId('bid')) === false) {
			$this->regionId = $this->session->getCurrentRegionId();
		}

		if ($this->session->isAmbassadorForRegion([$this->regionId], false, true) || $this->session->isOrgaTeam()) {
			$this->region = false;
			if ($region = $this->regionGateway->getRegion($this->regionId)) {
				$this->region = $region;
			}
		} else {
			$this->routeHelper->go('/?page=dashboard');
		}
	}

	public function index(): void
	{
		$this->pageHelper->addBread($this->region['name'], '/?page=bezirk&bid=' . $this->regionId);
		$this->pageHelper->addBread($this->translator->trans('pass.bread'));

		$this->pageHelper->addTitle($this->region['name']);
		$this->pageHelper->addTitle($this->translator->trans('pass.bread'));

		if (isset($_POST['passes']) && !empty($_POST['passes'])) {
			$this->generate($_POST['passes']);
		}

		if ($regions = $this->passportGeneratorGateway->getPassFoodsaver($this->regionId)) {
			$this->pageHelper->addHidden('
			<div id="verifyconfirm-dialog" title="' . $this->translator->trans('pass.verify.confirm') . '">'
				. $this->v_utils->v_info(
					'<p>' . $this->translator->trans('pass.verify.text') . '</p>',
					$this->translator->trans('pass.verify.confirm')
				) .
			'</div>');

			$this->pageHelper->addHidden('
			<div id="unverifyconfirm-dialog" title="' . $this->translator->trans('pass.verify.failed') . '">'
				. $this->v_utils->v_info(
					'<p>' . $this->translator->trans('pass.verify.checkPickups') . '</p>',
					$this->translator->trans('pass.verify.hasPickup')
				) .
			'</div>');

			$this->pageHelper->addContent('<form id="generate" method="post">');
			foreach ($regions as $region) {
				$this->pageHelper->addContent($this->view->passTable($region));
			}
			$this->pageHelper->addContent('</form>');
			$this->pageHelper->addContent($this->view->menubar(), CNT_RIGHT);
			$this->pageHelper->addContent($this->view->start(), CNT_RIGHT);
			$this->pageHelper->addContent($this->view->tips(), CNT_RIGHT);
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
		$tmp = [];
		foreach ($foodsavers as $foodsaver) {
			$tmp[$foodsaver] = (int)$foodsaver;
		}
		$foodsavers = $tmp;
		$is_generated = [];

		$pdf = new Fpdi();
		$pdf->AddPage();
		$pdf->SetTextColor(0, 0, 0);
		$pdf->AddFont('Ubuntu-L', '', 'lib/font/ubuntul.php', true);
		$pdf->AddFont('AcmeFont Regular', '', 'lib/font/acmefont.php', true);

		$x = 0;
		$y = 0;
		$card = 0;

		$noPhoto = [];

		end($foodsavers);

		$pdf->setSourceFile('img/foodsharing_logo.pdf');
		$fs_logo = $pdf->importPage(1);

		foreach ($foodsavers as $fs_id) {
			if ($foodsaver = $this->foodsaverGateway->getFoodsaverDetails($fs_id)) {
				if (empty($foodsaver['photo'])) {
					$noPhoto[] = $foodsaver['name'] . ' ' . $foodsaver['nachname'];

					$bellData = Bell::create(
						'passgen_failed_title',
						'passgen_failed',
						'fas fa-camera',
						['href' => '/?page=settings'],
						['user' => $this->session->user('name')],
						'pass-fail-' . $foodsaver['id']
					);
					$this->bellGateway->addBell($foodsaver['id'], $bellData);
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
					while ($pdf->GetStringWidth($foodsaver['name']) > $maxWidth
						|| $pdf->GetStringWidth($foodsaver['nachname']) > $maxWidth
					) {
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
				$pdf->Text(12.8 + $x, 18.6 + $y, $this->translator->trans('pass.claim'));

				$pdf->useTemplate($fs_logo, 13.5 + $x, 13.6 + $y, 29.8);

				$style = [
					'vpadding' => 'auto',
					'hpadding' => 'auto',
					'fgcolor' => [0, 0, 0],
					'bgcolor' => false, // array(255,255,255)
					'module_width' => 1, // width of a single module in points
					'module_height' => 1 // height of a single module in points
				];

				// FIXME Do we really always want fs.de here?!
				// QRCODE,L : QR-CODE Low error correction
				$pdf->write2DBarcode('https://foodsharing.de/profile/' . $fs_id, 'QRCODE,L', 70.5 + $x, 43 + $y, 20, 20, $style, 'N');

				if ($photo = $this->foodsaverGateway->getPhotoFileName($fs_id)) {
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
			$this->flashMessageHelper->info(
				$this->translator->trans('pass.noPhoto')
				. join(', ', $noPhoto)
				. $this->translator->trans('pass.notGenerated')
			);
		}

		$this->passportGeneratorGateway->updateLastGen($is_generated);

		$bez = strtolower($this->region['name']);

		$bez = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);

		$pdf->Output('foodsaver_pass_' . $bez . '.pdf', 'D');
		exit();
	}

	public function getRole(int $gender_id, int $role_id): string
	{
		switch ($gender_id) {
			case Gender::MALE:
			  $roles = [
					Role::FOODSHARER => $this->translator->trans('terminology.foodsharer.m'),
					Role::FOODSAVER => $this->translator->trans('terminology.foodsaver.m'),
					Role::STORE_MANAGER => $this->translator->trans('terminology.storemanager.m'),
					Role::AMBASSADOR => $this->translator->trans('terminology.ambassador.m'),
					Role::ORGA => $this->translator->trans('terminology.ambassador.m'),
				];
				break;

			case Gender::FEMALE:
			  $roles = [
					Role::FOODSHARER => $this->translator->trans('terminology.foodsharer.f'),
					Role::FOODSAVER => $this->translator->trans('terminology.foodsaver.f'),
					Role::STORE_MANAGER => $this->translator->trans('terminology.storemanager.f'),
					Role::AMBASSADOR => $this->translator->trans('terminology.ambassador.f'),
					Role::ORGA => $this->translator->trans('terminology.ambassador.f'),
				];
				break;

			// All others
			default:
				$roles = [
					Role::FOODSHARER => $this->translator->trans('terminology.foodsharer.d'),
					Role::FOODSAVER => $this->translator->trans('terminology.foodsaver.d'),
					Role::STORE_MANAGER => $this->translator->trans('terminology.storemanager.d'),
					Role::AMBASSADOR => $this->translator->trans('terminology.ambassador.d'),
					Role::ORGA => $this->translator->trans('terminology.ambassador.d'),
				];
			  break;
		}

		return $roles[$role_id];
	}

	private function download1(): void
	{
		$this->pageHelper->addJs('
			setTimeout(function(){goTo("/?page=passgen&bid=' . $this->regionId . '&dl2")},100);
		');
	}

	private function download2(): void
	{
		$bez = strtolower($this->region['name']);

		$bez = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);
		$file = 'data/pass/foodsaver_pass_' . $bez . '.pdf';

		$filename = basename($file);
		$size = filesize($file);
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename=' . $filename . '');
		header("Content-Length: $size");
		readfile($file);

		exit();
	}
}
