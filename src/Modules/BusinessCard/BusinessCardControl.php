<?php

namespace Foodsharing\Modules\BusinessCard;

use Foodsharing\Modules\Core\Control;
use setasign\Fpdi\Tcpdf\Fpdi;

class BusinessCardControl extends Control
{
	private $gateway;

	public function __construct(BusinessCardView $view, BusinessCardGateway $gateway)
	{
		$this->view = $view;
		$this->gateway = $gateway;

		parent::__construct();
	}

	public function index()
	{
		$this->pageHelper->addBread($this->translator->trans('bcard.title'));

		$this->pageHelper->addContent($this->view->top(), CNT_TOP);

		if ($data = $this->gateway->getMyData($this->session->id(), $this->session->may('bieb'))) {
			if (strlen($data['anschrift'] . ', ' . $data['plz'] . ' ' . $data['stadt']) >= 49) {
				$this->flashMessageHelper->error($this->translator->trans('bcard.error.length'));
				$this->routeHelper->go('/?page=settings');
			}
			if (strlen($data['telefon'] . $data['handy']) <= 3) {
				$this->flashMessageHelper->error($this->translator->trans('bcard.error.phone'));
				$this->routeHelper->go('/?page=settings');
			}
			if ($data['verified'] == 0) {
				$this->flashMessageHelper->error($this->translator->trans('bcard.error.verified'));
				$this->routeHelper->go('/?page=settings');
			}

			$choices = [];

			foreach ($data['bot'] as $b) {
				$choices[] = [
					'id' => 'bot:' . $b['id'],
					'name' => $this->translator->trans('bcard.for', [
						'{role}' => $this->translator->trans('terminology.ambassador.d'),
						'{region}' => $b['name'],
					]),
				];
			}
			foreach ($data['sm'] as $b) {
				$choices[] = [
					'id' => 'sm:' . $b['id'],
					'name' => $this->translator->trans('bcard.for', [
						'{role}' => $this->translator->trans('terminology.storemanager.d'),
						'{region}' => $b['name'],
					]),
				];
			}
			foreach ($data['fs'] as $b) {
				$choices[] = [
					'id' => 'fs:' . $b['id'],
					'name' => $this->translator->trans('bcard.for', [
						'{role}' => $this->translator->trans('terminology.foodsaver.d'),
						'{region}' => $b['name'],
					]),
				];
			}

			$this->pageHelper->addContent($this->view->optionForm($choices));
		}
	}

	public function makeCard()
	{
		$data = $this->gateway->getMyData($this->session->id(), $this->session->may('bieb'));
		$opt = $this->getRequest('opt');
		if (!$data || !$opt) {
			return;
		} else {
			$opt = explode(':', $opt); // role:region
		}

		if (count($opt) != 2 || (int)$opt[1] < 0) {
			return;
		}

		$regionId = (int)$opt[1];
		$role = $opt[0];
		$mailbox = false;

		if (isset($data[$role]) && $data[$role] != false) {
			foreach ($data[$role] as $d) {
				if ($d['id'] == $regionId) {
					$mailbox = $d;
				}
			}
		} else {
			return;
		}

		if (!$mailbox) {
			return;
		}

		if (isset($mailbox['email'])) {
			$data['email'] = $mailbox['email'];
		}
		$data['subtitle'] = $this->displayedRole($role, $data['geschlecht'], $mailbox['name']);

		return $this->generatePdf($data, $role);
	}

	private function displayedRole($role, $gender, $regionName)
	{
		$modifier = 'dmfd'[$gender]; // 0=d 1=m 2=f 3=d
		switch ($role) {
			case 'sm':
				$roleName = $this->translator->trans('terminology.storemanager.' . $modifier);
				break;
			case 'bot':
				$roleName = $this->translator->trans('terminology.ambassador.' . $modifier);
				break;
			case 'fs':
			default:
				$roleName = $this->translator->trans('terminology.foodsaver.' . $modifier);
				break;
		}

		return $this->translator->trans('bcard.for', ['{role}' => $roleName, '{region}' => $regionName]);
	}

	private function generatePdf($data, $role = 'fs')
	{
		$pdf = new Fpdi();
		$pdf->AddPage();
		$pdf->SetTextColor(0, 0, 0);
		$pdf->AddFont('Ubuntu-L', '', 'lib/font/ubuntul.php', true);
		$pdf->AddFont('AcmeFont Regular', '', 'lib/font/acmefont.php', true);

		$x = 0;
		$y = 0;

		for ($i = 0; $i < 8; ++$i) {
			$pdf->Image('img/fsvisite.png', 10 + $x, 10 + $y, 91, 61);

			$pdf->SetTextColor(85, 60, 36);

			if (strlen($data['name'] . ' ' . $data['nachname']) >= 33) {
				$pdf->SetFont('Ubuntu-L', '', 5);
				$pdf->Text(48.5 + $x, 29.5 + $y, $data['name'] . ' ' . $data['nachname']);
			} elseif (strlen($data['name'] . ' ' . $data['nachname']) >= 22) {
				$pdf->SetFont('Ubuntu-L', '', 7);
				$pdf->Text(48.5 + $x, 29.5 + $y, $data['name'] . ' ' . $data['nachname']);
			} else {
				$pdf->SetFont('Ubuntu-L', '', 10);
				$pdf->Text(48.5 + $x, 29.5 + $y, $data['name'] . ' ' . $data['nachname']);
			}

			$pdf->SetFont('Ubuntu-L', '', 7);
			if (strlen($data['anschrift'] . ', ' . $data['plz'] . ' ' . $data['stadt']) > 32) {
				$pdf->SetFont('Ubuntu-L', '', 6);
			}

			$pdf->SetXY(48.5 + $x, 35.2 + $y);
			$pdf->MultiCell(50, 12, $data['subtitle'], 0, 'L');

			$pdf->SetTextColor(0, 0, 0);
			$pdf->Text(52.3 + $x, 44.8 + $y, $data['anschrift']);
			$pdf->Text(52.3 + $x, 47.8 + $y, $data['plz'] . ' ' . $data['stadt']);
			$tel = $data['handy'];
			if (empty($tel)) {
				$tel = $data['telefon'];
			}

			$pdf->Text(52.3 + $x, 51.8 + $y, $tel);
			$pdf->Text(52.3 + $x, 56.2 + $y, $data['email']);
			$pdf->Text(52.3 + $x, 61.6 + $y, BASE_URL);
			if ($x == 0) {
				$x += 91;
			} else {
				$y += 61;
				$x = 0;
			}
		}

		$pdf->Output('bcard-' . $role . '.pdf', 'D');
	}
}
