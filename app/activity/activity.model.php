<?php
class ActivityModel extends Model
{
	
	public function loadMailboxUpdates()
	{
		$model = loadModel('mailbox');
		
		if($boxes = $model->getBoxes())
		{
			$mb_ids = array();
			foreach ($boxes as $b)
			{
				$mb_ids[] = $b['id'];
			}
			
			if($updates = $this->q('
				SELECT
					m.sender,
					m.subject,
					m.body,
					m.time,
					UNIX_TIMESTAMP(m.time) AS time_ts,
					b.name AS mb_name
			
				FROM
					'.PREFIX.'mailbox_message m,
					'.PREFIX.'mailbox b
			
				WHERE
					m.mailbox_id = b.id
			
				AND
					b.id IN('.implode(',',$mb_ids).')
					
				ORDER BY m.id DESC
		
				LIMIT 0, 10
			'))
			{
				$out = array();
				foreach ($updates as $u)
				{
					$out[$u['time']] = array(
							'attr' => array(
									'href' => '#'
							),
							'title' => $u['mb_name'].'@ Neue E-Mail '.$u['subject'],
							'desc' => trim(tt(strip_tags($u['body']),160)),
							'time' => $u['time'],
							'icon' => '/img/mailbox-50x50.png'
					);
				}
				
				return $out;
			}
			
		}
		
		return false;
	}
	
	public function loadForumUpdates()
	{
		$tmp = $this->getBezirkIds();
		$bids = array();
		foreach ($tmp as $t)
		{
			if($t > 0)
			{
				$bids[] = $t;
			}
		}
		
		if($updates = $this->q('
		
			SELECT 		t.id,
						t.name,
						t.`time`,
						UNIX_TIMESTAMP(t.`time`) AS time_ts,
						fs.id AS foodsaver_id,
						fs.name AS foodsaver_name,
						fs.photo AS foodsaver_photo,
						fs.sleep_status,
						p.body AS post_body,
						p.`time` AS update_time,
						UNIX_TIMESTAMP(p.`time`) AS update_time_ts,
						t.last_post_id,
						bt.bezirk_id,
						b.name AS bezirk_name,
						bt.bot_theme
		
			FROM 		'.PREFIX.'theme t,
						'.PREFIX.'theme_post p,
						'.PREFIX.'bezirk_has_theme bt,
						'.PREFIX.'foodsaver fs,
						'.PREFIX.'bezirk b
		
			WHERE 		t.last_post_id = p.id 		
			AND 		p.foodsaver_id = fs.id
			AND 		bt.theme_id = t.id
			AND 		bt.bezirk_id IN('.implode(',', $bids).')
			AND 		bt.bot_theme = 0
			AND 		bt.bezirk_id = b.id
			AND 		t.active = 1
		
			ORDER BY t.last_post_id DESC
		
			LIMIT 0, 10
		
		'))
		{
			$out = array();
			foreach ($updates as $u)
			{
				$check = true;
				$sub = 'forum';
				if($u['bot_theme'] == 1)
				{
					$sub = 'botforum';
					if(!isBotFor($u['bezirk_id']))
					{
						$check = false;
					}
				}

				$url = '/?page=bezirk&bid=' . (int)$u['bezirk_id'] . '&sub='.$sub.'&tid=' . (int)$u['id'] . '&pid='.(int)$u['last_post_id'].'#'.(int)$u['last_post_id'];
				
				if($check)
				{
					$out[$u['time']] = array(
							'attr' => array(
								'href' => $url
							),
							'title' => 'Antwort auf '.$u['name'].' im Forum '.$u['bezirk_name'] . ' von ' . $u['foodsaver_name'],
							'desc' => trim(tt(strip_tags($u['post_body']),160)),
							'time' => $u['time'],
							'icon' => img($u['foodsaver_photo'],50)
					);
				}
				
			}
			
			return $out;
		}
		
		return false;
	}
	
	public function loadBetriebUpdates()
	{
		if($bids = $this->getMyBetriebIds())
		{
			if($ret = $this->q('
			
			SELECT 	n.id, n.milestone, n.`text` , n.`zeit` AS update_time, UNIX_TIMESTAMP( n.`zeit` ) AS update_time_ts, fs.name AS foodsaver_name, fs.sleep_status, fs.id AS foodsaver_id, fs.photo AS foodsaver_photo, b.id AS betrieb_id, b.name AS betrieb_name
			FROM 	'.PREFIX.'betrieb_notiz n, '.PREFIX.'foodsaver fs, '.PREFIX.'betrieb b, '.PREFIX.'betrieb_team bt
			
			WHERE 	n.foodsaver_id = fs.id
			AND 	n.betrieb_id = b.id
			AND 	bt.betrieb_id = n.betrieb_id
			AND 	bt.foodsaver_id = '.(int)fsId().'
			AND 	n.milestone = 0
			AND 	n.last = 1
			
			ORDER BY n.id DESC
			LIMIT 0 , 10
			
		'))
			{
				$out = array();
				foreach ($ret as $r)
				{
					$out[$r['update_time']] = array(
							'attr' => array(
									'href' => '/?page=fsbetrieb&id=' . $r['betrieb_id']
							),
							'title' => $r['foodsaver_name'].' hat auf die Pinnwand von '.$r['betrieb_name'] . ' geschrieben',
							'desc' => trim(tt(strip_tags($r['text']),160)),
							'time' => $r['update_time'],
							'icon' => img($r['foodsaver_photo'],50)
					);
				}
					
				return $out;
			}
		}
		
		return false;
	}
}