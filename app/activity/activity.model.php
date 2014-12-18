<?php
class ActivityModel extends Model
{
	private $items_per_page = 10;
	
	public function loadMailboxUpdates($page = 0)
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
					m.id,
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
		
				LIMIT '.((int)$page*$this->items_per_page).', '.$this->items_per_page.'
			'))
			{
				$out = array();
				foreach ($updates as $u)
				{
					$sender = @json_decode($u['sender'],true);
					
					$from = 'E-Mail';
					
					if($sender != null)
					{
						if(isset($sender['from']) && !empty($sender['from']))
						{
							$from = '<a title="'.$sender['mailbox'].'@'.$sender['host'].'" href="/?page=mailbox&mailto='.urlencode($sender['mailbox'].'@'.$sender['host']).'">'.ttt($sender['personal'],22).'</a>';
						}
						else if(isset($sender['mailbox']))
						{
							$from = '<a title="'.$sender['mailbox'].'@'.$sender['host'].'" href="/?page=mailbox&mailto='.urlencode($sender['mailbox'].'@'.$sender['host']).'">'.ttt($sender['mailbox'].'@'.$sender['host'],22).'</a>';
						}
					}
					
					$out[] = array(
							'attr' => array(
									'href' => '/?page=mailbox&show=' . $u['id']
							),
							'title' => $from . ' <i class="fa fa-angle-right"></i> <a href="/?page=mailbox&show=' . $u['id'].'">'.ttt($u['subject'],30).'</a><small>'.ttt($u['mb_name'].'@'.DEFAULT_HOST,19).'</small>',
							'desc' => $this->textPrepare(nl2br($u['body'])),
							'time' => $u['time'],
							'icon' => '/img/mailbox-50x50.png',
							'time_ts' => $u['time_ts'],
							'quickreply' => '/xhrapp.php?app=mailbox&m=quickreply&mid=' . (int)$u['id']
					);
				}
				
				return $out;
			}
			
		}
		
		return false;
	}
	
	private function textPrepare($txt)
	{
		$txt = trim($txt);
		
		if(strlen($txt) > 100)
		{
			return '<span class="txt">'.tt(strip_tags($txt),90) . ' <a href="#" onclick="$(this).parent().hide().next().show();return false;">alles zeigen <i class="fa fa-angle-down"></i></a></span><span class="txt" style="display:none;">'.strip_tags($txt,'<br>').' <a href="#" onclick="$(this).parent().hide().prev().show();return false;">weniger <i class="fa fa-angle-up"></i></a></span>';
		}
		else 
		{
			return '<span class="txt">'.$txt.'</span>';
		}		
	}
	
	public function loadForumUpdates($page = 0)
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
		
			LIMIT '.((int)$page*$this->items_per_page).', '.$this->items_per_page.'
		
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

				$url = '/?page=bezirk&bid=' . (int)$u['bezirk_id'] . '&sub='.$sub.'&tid=' . (int)$u['id'] . '&pid='.(int)$u['last_post_id'].'#tpost-'.(int)$u['last_post_id'];
				
				if($check)
				{
					$out[] = array(
							'attr' => array(
								'href' => $url
							),
							'title' => '<a href="/profile/'.(int)$u['foodsaver_id'].'">'.$u['foodsaver_name'].'</a> <i class="fa fa-angle-right"></i> <a href="'.$url.'">'.$u['name'] . '</a> <small>'.$u['bezirk_name'] . '</small>',
							'desc' => $this->textPrepare($u['post_body']),
							'time' => $u['update_time'],
							'icon' => img($u['foodsaver_photo'],50),
							'time_ts' => $u['update_time_ts'],
							'quickreply' => '/xhrapp.php?app=bezirk&m=quickreply&bid=' . (int)$u['bezirk_id'] . '&tid=' . (int)$u['id'] . '&pid=' . (int)$u['last_post_id'] . '&sub=' . $sub
					);
				}
				
			}
			
			return $out;
		}
		
		return false;
	}
	
	public function loadBetriebUpdates($page = 0)
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
			LIMIT '.((int)$page*$this->items_per_page).', '.$this->items_per_page.'
			
		'))
			{
				$out = array();
				foreach ($ret as $r)
				{
					$out[] = array(
							'attr' => array(
									'href' => '/?page=fsbetrieb&id=' . $r['betrieb_id']
							),
							'title' => '<a href="/profile/'.$r['foodsaver_id'].'">'.$r['foodsaver_name'].'</a> <i class="fa fa-angle-right"></i> <a href="/?page=fsbetrieb&id=' . $r['betrieb_id'].'">'.$r['betrieb_name'].'</a>',
							'desc' => $this->textPrepare($r['text']),
							'time' => $r['update_time'],
							'icon' => img($r['foodsaver_photo'],50),
							'time_ts' => $r['update_time_ts']
					);
				}
					
				return $out;
			}
		}
		
		return false;
	}
}