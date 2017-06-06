<?php
class TeamModel extends Model
{
	public function getTeam($bezirkId = 1373)
	{
		$out = array();
		if($orgas =  $this->q('
				SELECT 
					fs.id, 
					CONCAT(mb.name,"@'.DEFAULT_HOST.'") AS email, 
					fs.name,
					fs.nachname,
					fs.photo,
					fs.about_me_public AS `desc`,
					fs.rolle,
					fs.geschlecht,
					fs.homepage,
					fs.github,
					fs.tox,
					fs.twitter,
					fs.position,
					fs.contact_public
				
				FROM 
					'.PREFIX.'foodsaver_has_bezirk hb

				LEFT JOIN
					'.PREFIX.'foodsaver fs
				ON
					hb.foodsaver_id = fs.id
				
				LEFT JOIN
					'.PREFIX.'mailbox mb 
				ON 
					fs.mailbox_id = mb.id

				WHERE 
					hb.bezirk_id = '.$bezirkId.'
				ORDER BY fs.name
		'))
		{
			foreach ($orgas as $o)
			{
				$out[(int)$o['id']] = $o;
			}
			
			
		}
		
		return $out;
	}
	
	public function getUser($id)
	{
		if($user = $this->qRow('
			SELECT 
				fs.id,
				CONCAT(fs.name," ",fs.nachname) AS name,
				fs.about_me_public AS `desc`,
				fs.rolle,
				fs.geschlecht,
				fs.photo,
				fs.twitter,
				fs.tox,
				fs.homepage,
				fs.github,
				fs.position,
				fs.email,
				fs.contact_public
				
			FROM 
				'.PREFIX.'foodsaver fs
				
			WHERE 
				fs.id = '.(int)$id.'
				
			AND 
				fs.rolle >= 3
		'))
		{
			$user['groups'] = $this->q('
				SELECT 
					b.id,
					b.name,
					b.type
						
				FROM 
					'.PREFIX.'botschafter bot,
					'.PREFIX.'bezirk b
						
				WHERE 
					bot.bezirk_id = b.id
						
				AND 
					bot.foodsaver_id = '.(int)$id.'
					
				AND 
					b.type = 7');
			
			return $user;
		}
	}
}
