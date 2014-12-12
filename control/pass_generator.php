<?php

$title = s('pass_generator');
addBread($title);

if(!isset($_GET['bid']))
{
	$bezirk_id = getBezirkId();
}
else
{
	$bezirk_id = (int)$_GET['bid'];
}

if(isBotFor($bezirk_id) || isOrgaTeam())
{
	if($bezirk_id > 0)
	{
		handle_pass_generator($bezirk_id);
	}
	else
	{
		error('Konnte Bezirk nicht ermitteln');
	}
	$cnt = array();
	if($bez = $db->getPassGenReq($bezirk_id))
	{
		addJs('
			$("label.checkall").click(function(){
				if( $(this).children("input")[0].checked )
				{
					$("ul.cblist input[type=\'checkbox\']").prop("checked", true);
				}
				else
				{
					$("ul.cblist input[type=\'checkbox\']").prop("checked", false);
				}
			});	
		');
		foreach ($bez as $b)
		{
			
			$inner = '<ul class="cblist"><li><label class="checkall"><input type="checkbox" class="checkall" /> <strong>Alle Auswählen</strong></label></li>';
			foreach ($b['foodsaver'] as $f)
			{
				$inner .= '<li><label><input class="fsch-'.$b['id'].'" type="checkbox" name="choose_foodsaver[]" value="'.$f['id'].'" /> '.$f['name'].'</label></li>';
			}
			$inner .= '<li><label class="checkall"><input type="checkbox" class="checkall" /> <strong>Alle Auswählen</strong></label></li></ul>';
			$cnt[] = v_field($inner, 'Foodsaver aus '.$b['bezirk']);
		}
	}
	
	addContent(v_form('passForm', $cnt));
	
	
	/*
	$content = v_quickform($title, array(
			v_form_checkbox('choose_foodsaver',array('values'=>$foodsaver,'checkall'=>true))
			
	));
	*/
}
else
{
	go('/');
}

function handle_pass_generator($bezirk_id)
{
	global $db;
	if(submitted())
	{
		$bezirk = $db->getBezirk($bezirk_id);
		
		if(!validEmail($bezirk['email']))
		{
			//orgaGlocke('Bezirk hat noch keine E-Mail Adresse',$bezirk['name'],'/?page=region');
			//error('Dein Bezirk hat noch keine E-Mail Adresse');
			//return false;
		}
		
		$email = $bezirk['email'];
		
		$foodsaver = getPost('choose_foodsaver');
		
		require('lib/fpdf.php');
		
		$pdf = new FPDF();
		$pdf->AddPage();
		$pdf->SetTextColor(85,60,36);
		
		
		
		$x = 0;
		$y = 0;
		$card = 0;
		
		$left = 0;
		$nophoto = array();
		$nofsid = array();

		$last = end($foodsaver);
		
		
		foreach ($foodsaver as $i => $fs_id)
		{			
			if($fs = $db->qRow('SELECT `fs_id`,`photo`,`id`,`name`,`nachname`,`autokennzeichen_id`,`geschlecht` FROM '.PREFIX.'foodsaver WHERE `id` = '.(int)$fs_id.' '))
			{
				if(empty($fs['photo']))
				{
					$nophoto[] = $fs['name'].' '.$fs['nachname'];
					$db->addGlocke($fs['id'], 'Dein Ausweis konnte nicht erstellt werden','Du hast noch kein Foto hochgeladen','/?page=settings');
					continue;
				}
				if(preg_replace('/[^0-9]/','',$fs['fs_id']) == '')
				{
					$nofsid[] = $fs['name'].' '.$fs['nachname'];
					$msg = 'Du musst Deine foodsharing ID eintragen';
					if(!empty($fs['fs_id']))
					{
						$msg = 'Deine Foodsharing ID ist ungültig';
					}
					$db->addGlocke($fs['id'],'Dein Ausweis konnte nicht erstellt werden', $msg,'/?page=settings');
					continue;
				}
				
				$card++;

				$db->passGen($fs['id']);
				
				$pdf->Image('img/foodsaver_pass.png',10+$x,10+$y,90,60);
				
				$pdf->SetFont('Arial','',9);
				if($fs['geschlecht'] == 2)
				{
					$pdf->Text(15+$x, 39+$y, utf8_decode('akkreditierte Lebensmittelretterin'));
				}
				else 
				{
					$pdf->Text(15+$x, 39+$y, utf8_decode('akkreditierter Lebensmittelretter'));
				}
				
				$pdf->SetFont('Arial','B',12);
				$pdf->Text(15+$x, 46.5+$y, utf8_decode($fs['name'].' '.$fs['nachname']));
				$pdf->Text(15+$x, 52+$y, utf8_decode('ID: '.$db->getKfz($fs['autokennzeichen_id']).'-'.$fs['fs_id'].'-'.$fs['id']));
				
				$pdf->SetFont('Arial','',9);
				$pdf->Text(15+$x, 59+$y, utf8_decode('Essen teilen, statt wegwerfen'));
				$pdf->Text(15+$x, 63+$y, utf8_decode('www.lebensmittelretten.de'));
				
				$pdf->Text(67+$x, 63+$y, utf8_decode('gültig ab '.date('d.m.Y')));
				
				if($photo = $db->getPhoto($fs_id))
				{
					if(file_exists('images/crop_'.$photo))
					{
						$pdf->Image('images/crop_'.$photo,73+$x,24+$y,22);
					}
					elseif(file_exists('images/'.$photo))
					{
						$pdf->Image('images/'.$photo,73+$x,24+$y,22);
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
	
		//$bezirk = $db->getBezirkName();
		
		$bez = strtolower($bezirk['name']);
		
		$bez = str_replace(array('ä','ö','ü','ß'), array('ae','oe','ue','ss'), $bez);
		$bez = preg_replace('/[^a-zA-Z]/', '', $bez);
		
		//Header('Content-Type: application/pdf');
		//Header('Content-Length: ' . strlen($pdf->buffer));
		//Header('Content-disposition: attachment; filename=foodsaver_pass_'.$bezirk.'.pdf');
		//echo $pdf->buffer;
		$file = 'data/pass/foodsaver_pass_'.$bez.'.pdf';
		
		$pdf->Output('data/pass/foodsaver_pass_'.$bez.'.pdf');
		//go('data/pass/foodsaver_pass_'.$bezirk.'.pdf');
		
		
		$Dateiname = basename($file);
		$size = filesize($file);
		header('Content-Type: application/pdf');
		header("Content-Disposition: attachment; filename=".$Dateiname."");
		header("Content-Length: $size");
		readfile($file);
		
		exit();
	}
}