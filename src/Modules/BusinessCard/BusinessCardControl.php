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
		$this->pageHelper->addBread($this->translationHelper->s('bcard_generator'));

		$this->pageHelper->addContent($this->view->top(), CNT_TOP);

		if ($data = $this->gateway->getMyData($this->session->id(), $this->session->may('bieb'))) {
			if (strlen($data['anschrift'] . ', ' . $data['plz'] . ' ' . $data['stadt']) >= 49) {
				$this->flashMessageHelper->error('Deine Anschrift ist zu lang! Anschrift, Postleitzahl und Stadt dürfen zusammen maximal 49 Zeichen haben.');
				$this->routeHelper->go('/?page=settings');
			}
			if (strlen($data['telefon'] . $data['handy']) <= 3) {
				$this->flashMessageHelper->error('Du musst eine gültige Telefonnummer angegeben haben, um Deine Visitenkarte zu generieren');
				$this->routeHelper->go('/?page=settings');
			}
			if ($data['verified'] == 0) {
				// you have to be a verified user to generate your business card.
				$this->flashMessageHelper->error('Du musst verifiziert sein, um Deine Visitenkarte generieren zu können.');
				$this->routeHelper->go('/?page=settings');
			}
			$sel_data = [];
			if ($data['bot']) {
				foreach ($data['bot'] as $b) {
					$sel_data[] = [
						'id' => 'bot:' . $b['id'],
						'name' => $this->translationHelper->sv('bot_for', $b['name'])
					];
				}
			}

			if ($data['sm']) {
				foreach ($data['sm'] as $fs) {
					$sel_data[] = [
						'id' => 'sm:' . $fs['id'],
						'name' => $this->translationHelper->sv('sm_for', $fs['name'])
					];
				}
			}
			if ($data['fs']) {
				foreach ($data['fs'] as $fs) {
					$sel_data[] = [
						'id' => 'fs:' . $fs['id'],
						'name' => $this->translationHelper->sv('fs_for', $fs['name'])
					];
				}
			}

			$this->pageHelper->addContent($this->view->optionForm($sel_data));
		}
	}

	public function makeCard()
	{
		if (($data = $this->gateway->getMyData($this->session->id(), $this->session->may('bieb'))) && ($opt = $this->getRequest('opt'))) {
			$opt = explode(':', $opt);
			if (count($opt) == 2 && (int)$opt[1] > 0) {
				$id = (int)$opt[1];
				$type = $opt[0];
				$mailbox = false;
				if (isset($data[$type]) && $data[$type] != false) {
					foreach ($data[$type] as $d) {
						if ($d['id'] == $id) {
							$mailbox = $d;
						}
					}
				} else {
					return false;
				}
				if ($mailbox !== false) {
					if ($type == 'fs') {
						if ($data['geschlecht'] == 2) {
							$data['subtitle'] = $this->translationHelper->sv('fs_for_w', $mailbox['name']);
						} else {
							$data['subtitle'] = $this->translationHelper->sv('fs_for', $mailbox['name']);
						}
					} elseif ($type == 'sm') {
						if ($data['geschlecht'] == 2) {
							$data['subtitle'] = $this->translationHelper->sv('sm_for_w', $mailbox['name']);
						} else {
							$data['subtitle'] = $this->translationHelper->sv('sm_for', $mailbox['name']);
						}
					} elseif ($type == 'bot') {
						if ($data['geschlecht'] == 2) {
							$data['subtitle'] = $this->translationHelper->sv('bot_for_w', $mailbox['name']);
						} else {
							$data['subtitle'] = $this->translationHelper->sv('bot_for', $mailbox['name']);
						}
						$data['email'] = $mailbox['email'];
					} else {
						return false;
					}
				}

				return $this->generatePdf($data, $type);
			}
		}
	}

	private function generatePdf($data, $type = 'fs')
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

		$pdf->Output('bcard-' . $type . '.pdf', 'D');
	}
}
