<?php 

function getField($field)
{
	$data = getCsvFile();
	$out = array();
	foreach($data as $d)
	{
		if(isset($d[$field]) && !empty($d[$field]) && validEmail($d[$field]))
		{
			$out[] = $d[$field];
		}
	}
	
	return $out;
}

function importUsers()
{
	$csv = getCsvFile();
	
	foreach ($csv as $c)
	{
		global $db;
		
		$md5 = $db->encryptMd5(trim($c['emailadresse']), 'EssenRetten2013');
		
		$adresse = array();
		preg_match("/([a-zA-Z\s\.\-\ß]+)\s(.*[0-9]+.*)/is", $c['straeundhausnummer'], $adresse);

		
		if(empty($adresse) || count($adresse) < 3)
		{
			$adresse = array(
					$c['straeundhausnummer'],
					$c['straeundhausnummer'],
					''
			);
		}
		
		$gesch = 0;
		if($c['geschlecht'] == 'Frau')
		{
			$gesch = 1;
		}
		elseif($c['geschlecht'] == 'Mann')
		{
			$gesch = 2;
		}
		
		$geb_datum = explode('/',$c['geburtsdatum']);
		
		if(count($geb_datum) == 3)
		{
			$geb_datum = date('Y-m-d',strtotime($geb_datum[2].'-'.$geb_datum[1].'-'.$geb_datum[0]));
		}
		else
		{
			$geb_datum = '';
		}
		
		$c['postleitzahl'] = trim($c['postleitzahl']);
		if(strlen($c['postleitzahl']) == 5)
		{
			$plz_id = $db->addOrGetPlzId($c['postleitzahl'],$c['stadt']);
			$plz_id = $plz_id['id'];
		}
		else
		{
			$plz_id = 0;
		}
		
		$bezirk_id = 0;
		if($plz_id > 0)
		{
			$bezirk_id = $db->getBezirkIdByPlz($plz_id);
		}
		
	$db->insert('
		INSERT INTO `fs_foodsaver`
		(	 	
			plz_id, 
			bezirk_id, 
			email, 
			passwd, 
			name, 
			admin, 
			nachname, 
			str, 
			hsnr, 
			telefon, 
			handy,
			geschlecht, 
			geb_datum
		)
		VALUES
		(
			'.$db->intval($plz_id).', 
			'.$db->intval($bezirk_id).', 
			'.$db->strval($c['emailadresse']).', 
			'.$db->strval($md5).', 
			'.$db->strval($c['vorname']).', 
			0, 
			'.$db->strval($c['nachname']).', 
			'.$db->strval($adresse[1]).', 
			'.$db->strval($adresse[2]).', 
			'.$db->strval($c['festnetznummer']).',
			'.$db->strval($c['handynummer']).',
			'.$db->intval($gesch).', 
			'.$db->dateval($geb_datum).'
		)');
	}
}

function getCsvFile()
{
	$row = 1;
	$assoc = array();
	$out = array();
	if (($handle = fopen("data/koeln.csv", "r")) !== FALSE) 
	{
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) 
	    {
	    	if($row == 1)
	    	{
	    		$num = count($data);
	    		for ($c=0; $c < $num; $c++)
	    		{
	    			$assoc[$c] = makeId($data[$c]);
	    		}
	    	}
	    	else
	    	{
	    		if(count($data) < 30)
	    		{
	    			continue;
	    		}
	    		$out[$row] = array();
	    		$num = count($data);
	    		for ($c=0; $c < $num; $c++)
	    		{
	    			$out[$row][$assoc[$c]] = $data[$c];
	    		}
	    	}
	        $row++;
	    }
	    
	    fclose($handle);
	}
	
	return $out;
}

function get($name)
{
	$out = array();
	$fp = fopen('data/'.$name.'.csv','r');
	while(($data = fgetcsv($fp, 1000, ",")) !== FALSE)
	{
		$out[] = $data;
	}
	
	$out = array_reverse($out);
	
	return $out;
}

function put($name,$data)
{
	$fp = fopen('data/'.$name.'.csv','a');
	fputcsv($fp,$data);
	fclose($fp);
}

?>