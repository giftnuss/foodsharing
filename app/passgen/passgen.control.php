<?php
class PassgenControl extends Control
{
	private $bezirk_id;
	private $bezirk;
	
	public function __construct()
	{
		$this->model = new PassgenModel();
		$this->view = new PassgenView();
		
		parent::__construct();
		
		$this->bezirk_id = false;
		if(($this->bezirk_id = getGetId('bid')) === false)
		{
			$this->bezirk_id = getBezirkId();
		}
		
		if(isBotFor($this->bezirk_id) || isOrgaTeam())
		{
			$this->bezirk = false;
			if($bezirk = $this->model->getBezirk($this->bezirk_id))
			{
				$this->bezirk = $bezirk;
			}
		}
		else
		{
			go('/?page=dashboard');
		}
	}
	
	public function index()
	{	
		addBread($this->bezirk['name'],'/?page=bezirk&bid=' . $this->bezirk_id . '&sub=forum');
		addBread('Pass-Generator',getSelf());
		
		addTitle($this->bezirk['name']);
		addTitle('Pass Generator');
		
		if(isset($_POST['foods']) && !empty($_POST['foods']))
		{
			$this->generate($_POST['foods']);
		}
		
		if($bezirke = $this->model->getPassFoodsaver($this->bezirk_id))
		{
			addHidden('
			<div id="verifyconfirm-dialog" title="'.s('verify_confirm_title').'">
				'.v_info('<p>'.s('verify_confirm').'</p>',s('verify_confirm_title')).'
				<span class="button_confirm" style="display:none">'.s('verify_confirm_button').'</span>
				<span class="button_abort" style="display:none">'.s('abort').'</span>
			</div>');
			
			addContent('<form id="generate" method="post">');
			foreach ($bezirke as $b)
			{
				addContent($this->view->passTable($b));
			}
			addContent('</form>');
			addContent($this->view->menubar(),CNT_RIGHT);
			addContent($this->view->start(),CNT_RIGHT);
			addContent($this->view->tips(),CNT_RIGHT);
		}
		
		if(isset($_GET['dl1']))
		{
			$this->download1();	
		}
		if(isset($_GET['dl2']))
		{
			$this->download2();
		}
	}
	
	public function generate($foodsaver)
	{	
		$tmp = array();
		foreach ($foodsaver as  $fs)
		{
			$tmp[$fs] = (int)$fs;
		}
		$foodsaver = $tmp;
		$is_generated = array();
		
		if(!validEmail($this->bezirk['email']))
		{
			//orgaGlocke('Bezirk hat noch keine E-Mail Adresse',$bezirk['name'],'/?page=region');
			//error('Dein Bezirk hat noch keine E-Mail Adresse');
			//return false;
		}
		
		$email = $this->bezirk['email'];
		include('lib/phpqrcode/qrlib.php');
		
		require_once('lib/fpdf.php');
		require_once('lib/fpdi/fpdi.php');
		
		$pdf = new FPDI();
		$pdf->AddPage();
		$pdf->SetTextColor(0,0,0);
		$pdf->AddFont('Ubuntu-L','','Ubuntu-L.php');
		$pdf->AddFont('AcmeFont Regular','','acmefontregular.php');
		
		$x = 0;
		$y = 0;
		$card = 0;
		
		$left = 0;
		$nophoto = array();
		$nofsid = array();
		
		$last = end($foodsaver);
		
		$pageCount = $pdf->setSourceFile('img/foodsharing_logo.pdf');
		$fs_logo = $pdf->importPage(1);
		
		foreach ($foodsaver as $i => $fs_id)
		{	
			if($fs = $this->model->qRow('SELECT `fs_id`,`photo`,`id`,`name`,`nachname`,`autokennzeichen_id`,`geschlecht`,`rolle` FROM '.PREFIX.'foodsaver WHERE `id` = '.(int)$fs_id.' '))
			{
				if(empty($fs['photo']))
				{
					$nophoto[] = $fs['name'].' '.$fs['nachname'];
					
					$this->model->addBell(
						$fs['id'],
						'passgen_failed_title', 
						'passgen_failed', 
						'fa fa-camera', 
						array('href' => '/?page=settings'), 
						array('user' => S::user('name')),
						'pass-fail-'.$fs['id']
					);
					continue;
				}

				$pdf->SetTextColor(0,0,0);
				$pdf->AddFont('Ubuntu-L','','Ubuntu-L.php');
				
				@unlink('tmp/qr_'.$fs_id.'.png');
				QRcode::png($fs_id,'tmp/qr_'.$fs_id.'.png',QR_ECLEVEL_L,3.4,0);
				
				$card++;
		
				$this->model->passGen($fs['id']);
		
				$pdf->Image('img/pass_bg.png',10+$x,10+$y,83,55);
		
				$pdf->SetFont('Ubuntu-L','',10);
				
				$pdf->Text(41.8+$x, 34.5+$y, utf8_decode($fs['name'].' '.$fs['nachname']));
				$pdf->Text(41.8+$x, 42.3+$y, utf8_decode($this->getRolle($fs['geschlecht'], $fs['rolle'])));
				$pdf->Text(41.8+$x, 50.1+$y, utf8_decode(date('d. m. Y',time()-1814400)));
				$pdf->Text(41.8+$x, 57.9+$y, utf8_decode(date('d. m. Y',time()+94608000)));
				
				$pdf->SetFont('Ubuntu-L','',6);
				$pdf->Text(41.8+$x, 31.2+$y, utf8_decode('Name'));
				$pdf->Text(41.8+$x, 39.0+$y, utf8_decode('Rolle'));
				$pdf->Text(41.8+$x, 46.8+$y, utf8_decode('Gültig ab'));
				$pdf->Text(41.8+$x, 54.6+$y, utf8_decode('Gültig bis'));
				//$pdf->Text(15+$x, 52+$y, utf8_decode('ID: '.$this->model->getKfz($fs['autokennzeichen_id']).'-'.$fs['fs_id'].'-'.$fs['id']));
		
				$pdf->SetFont('Ubuntu-L','',9);
				$pdf->SetTextColor(255,255,255);
				$pdf->SetXY(40+$x, 13.2+$y);
				$pdf->Cell(50,5,'ID '.$fs_id, 0,0, 'R');
				
				$pdf->SetFont('AcmeFont Regular','',5.3);
				$pdf->Text(13.9+$x, 20.6+$y, 'Teile Lebensmittel, anstatt sie wegzuwerfen!');
				
				$pdf->useTemplate($fs_logo, 13.5+$x, 13.6+$y, 29.8);
				
				//$pdf->Text(15+$x, 59+$y, utf8_decode('Essen teilen, statt wegwerfen'));
				//$pdf->Text(15+$x, 63+$y, utf8_decode('www.lebensmittelretten.de'));
		
				//$pdf->Text(67+$x, 63+$y, utf8_decode('gültig ab '.date('d.m.Y')));
		
				$pdf->Image('tmp/qr_'.$fs_id.'.png',70+$x,42.0+$y);
				
				if($photo = $this->model->getPhoto($fs_id))
				{
					if(file_exists('images/crop_'.$photo))
					{
						$pdf->Image('images/crop_'.$photo,14+$x,29.7+$y,24);
					}
					elseif(file_exists('images/'.$photo))
					{
						$pdf->Image('images/'.$photo,14+$x,29.7+$y,22);
					}
				}
		
				if($x == 0)
				{
					$x += 95;
				}
				else
				{
					$y += 65;
					$x = 0;
				}
		
				if($card == 8)
				{
					$card = 0;
					$pdf->AddPage();
					$x = 0;
					$y = 0;
				}
				
				$is_generated[] = $fs['id'];
				//$x += 95;
					
			}
			//$pdf->SetFont('Arial','',13);
			//$pdf->Cell(40,10,utf8_decode($fs['name'].' '.$fs['nachname']));
			//$pdf->Image('img/foodsaver_pass_bg.png');
		}
		if(!empty($nophoto))
		{
			$last = array_pop($nophoto);
			info(implode(', ', $nophoto).' und '.$last.' haben noch kein Photo hochgeladen und ihr Ausweis konnte nicht erstellt werden');
		}
		if(!empty($nofsid))
		{
			$last = array_pop($nofsid);
			info(implode(', ', $nofsid).' und '.$last.' haben eine ungültige Foodsharing ID, ihr Ausweis wurde nicht erstellt');
		}
		
		$this->model->updateLastGen($is_generated);
		
		//$bezirk = $db->getBezirkName();
		
		$bez = strtolower($this->bezirk['name']);
		
		$bez = str_replace(array('ä','ö','ü','ß'), array('ae','oe','ue','ss'), $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);
		
		//Header('Content-Type: application/pdf');
		//Header('Content-Length: ' . strlen($pdf->buffer));
		//Header('Content-disposition: attachment; filename=foodsaver_pass_'.$bezirk.'.pdf');
		//echo $pdf->buffer;
		$file = 'data/pass/foodsaver_pass_'.$bez.'.pdf';
		
		$pdf->Output('data/pass/foodsaver_pass_'.$bez.'.pdf');
		//go('data/pass/foodsaver_pass_'.$bezirk.'.pdf');
		
		/*
		$Dateiname = basename($file);
		$size = filesize($file);
		header('Content-Type: application/pdf');
		header("Content-Disposition: attachment; filename=".$Dateiname."");
		header("Content-Length: $size");
		readfile($file);
		
		exit();
		*/
		go(getSelf().'&dl1');
	}
	
	public function getRolle($gender_id,$rolle_id)
	{
		$rolle = array(
				0 => array(
						0 => 'Freiwilliger',
						1 => 'Foodsaver',
						2 => 'Betriebsverantwortliche/r',
						3 => 'Botschafter'
				),
				1 => array(
						0 => 'Freiwilliger',
						1 => 'Foodsaver',
						2 => 'Betriebsverantwortlicher',
						3 => 'Botschafter'
				),
				2 => array(
						0 => 'Freiwillige',
						1 => 'Foodsaver',
						2 => 'Betriebsverantwortliche',
						3 => 'Botschafterin'
				),
				3 => array(
						0 => 'Freiwillige/r',
						1 => 'Foodsaver',
						2 => 'Betriebsverantwortliche/r',
						3 => 'Botschafte/r'
				)
		);
		return $rolle[$gender_id][$rolle_id];
	}
	
	public function download1()
	{
		addJs('
			setTimeout(function(){goTo("/?page=passgen&bid='.$this->bezirk_id.'&dl2")},100);		
		');
	}
	public function download2()
	{
		$bez = strtolower($this->bezirk['name']);
		
		$bez = str_replace(array('ä','ö','ü','ß'), array('ae','oe','ue','ss'), $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);
		$file = 'data/pass/foodsaver_pass_'.$bez.'.pdf';
		
		$Dateiname = basename($file);
		$size = filesize($file);
		header('Content-Type: application/pdf');
		header("Content-Disposition: attachment; filename=".$Dateiname."");
		header("Content-Length: $size");
		readfile($file);
		
		exit();
	}
}
