<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Lib\Session\S;
use Foodsharing\Modules\Core\View;

class DashboardView extends View
{
	public function newBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b) {
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . '});return false;" href="#" class="corner-all">
					<span class="i">' . $this->img($b) . '</span>
					<span class="n">Essenskorb von ' . $b['fs_name'] . '</span>
					<span class="t">veröffentlicht am ' . $this->func->niceDate($b['time_ts']) . '</span>
					<span class="d">' . $b['description'] . '</span>
					<span class="c"></span>
				</a>
	
			</li>';
		}

		$out .= '
				</ul>';

		return $this->v_utils->v_field($out, $this->func->s('new_foodbaskets'));
	}

	public function updates()
	{
		$this->func->addStyle('
		#activity ul.linklist li span.time{margin-left:58px;display:block;margin-top:10px;}
		
		#activity ul.linklist li span.qr
		{
			margin-left:58px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			border-radius: 3px;
			opacity:0.5;
		}
		
		#activity ul.linklist li span.qr:hover
		{
			opacity:1;
		}
		
		#activity ul.linklist li span.qr img
		{
			height:32px;
			width:32px;
			margin-right:-35px;
			border-right:1px solid #ffffff;
			-webkit-border-top-left-radius: 3px;
			-webkit-border-bottom-left-radius: 3px;
			-moz-border-radius-topleft: 3px;
			-moz-border-radius-bottomleft: 3px;
			border-top-left-radius: 3px;
			border-bottom-left-radius: 3px;
		}
		#activity ul.linklist li span.qr textarea, #activity ul.linklist li span.qr .loader
		{
			border: 0 none;
		    height: 16px;
		    margin-left: 36px;
		    padding: 8px;
		    width: 78.6%;
			-webkit-border-top-right-radius: 3px;
			-webkit-border-bottom-right-radius: 3px;
			-moz-border-radius-topright: 3px;
			-moz-border-radius-bottomright: 3px;
			border-top-right-radius: 3px;
			border-bottom-right-radius: 3px;
			margin-right:-30px;
			background-color:#F9F9F9;
		}
		
		#activity ul.linklist li span.qr .loader
		{
			background-color: #ffffff;
		    position: relative;
		    text-align: left;
		    top: -10px;
		}
		
		#activity ul.linklist li span.t span.txt {
		    overflow: hidden;
		    text-overflow: unset;
    		white-space: normal;
			padding-left:10px;
			border-left:2px solid #4A3520;
			margin-bottom:10px;
			display:block;
		}
		#activity ul.linklist li span
		{
			color:#4A3520;
		}
		#activity ul.linklist li span a
		{
			color:#46891b !important;
		}
		#activity span.n i.fa
		{
			display:inline-block;
			width:11px;
			text-align:center;
		}
		#activity span.n small
		{
			float:right;
			opacity:0.8;
			font-size:12px;
		}
		#activity ul.linklist li span a:hover
		{
			text-decoration:underline !important;
			color:#46891b !important;
		}
		
		#activity ul.linklist li
		{
			margin-bottom:10px;
			background-color:#ffffff;
			padding:10px;
			-webkit-border-radius: 6px;
			-moz-border-radius: 6px;
			border-radius: 6px;
		}
		
		ul.linklist li span.n
		{
			font-weight:normal;
			font-size:13px;
			margin-bottom:10px;
			text-overflow: unset;
    		white-space: inherit;
		}
		
		@media (max-width: 900px)
		{
			#activity ul.linklist li span.qr textarea, #activity ul.linklist li span.qr .loader
			{
				width:74.6%;
			}
		}
		@media (max-width: 400px)
		{
			ul.linklist li span.n
			{
				height:55px;
			}
			#activity ul.linklist li span.qr textarea, #activity ul.linklist li span.qr .loader
			{
				width:82%;
			}
			#activity ul.linklist li span.time, #activity ul.linklist li span.qr
			{
				margin-left:0px;
			}
			#activity span.n small
			{
				float:none;
				display:block;
			}
		}
	');
		$this->func->addScript('/js/jquery.tinysort.min.js');
		$this->func->addScript('/js/activity.js');
		$this->func->addJs('activity.init();');
		$this->func->addContent('
	<div class="head ui-widget-header ui-corner-top">
		Updates-Übersicht<span class="option"><a id="activity-option" href="#activity-listings" class="fa fa-gear"></a></span>
	</div>
	<div id="activity">
		<div class="loader" style="padding:40px;background-image:url(/img/469.gif);background-repeat:no-repeat;background-position:center;"></div>
		<div style="display:none" id="activity-info">' . $this->v_utils->v_info('Es gibt gerade nichts Neues') . '</div>
	</div>');
	}

	public function foodsharerMenu()
	{
		return $this->menu(array(
			array('name' => $this->func->s('new_basket'), 'click' => "ajreq('newbasket',{app:'basket'});return false;"),
			array('name' => $this->func->s('all_baskets'), 'href' => '/karte?load=baskets')
		));
	}

	public function closeBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b) {
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . '});return false;" href="#" class="corner-all">
					<span class="i">' . $this->img($b) . '</span>
					<span class="n">Essenskorb von ' . $b['fs_name'] . ' (' . $this->distance($b['distance']) . ')</span>
					<span class="t">' . $this->func->niceDate($b['time_ts']) . '</span>
					<span class="d">' . $b['description'] . '</span>
					<span class="c"></span>
				</a>
	
			</li>';
		}

		$out .= '
				</ul>';

		return $this->v_utils->v_field($out, $this->func->s('close_foodbaskets'));
	}

	private function img($basket)
	{
		if ($basket['picture'] != '' && file_exists(ROOT_DIR . 'images/basket/50x50-' . $basket['picture'])) {
			return '<img src="/images/basket/thumb-' . $basket['picture'] . '" height="50" />';
		}

		return '<img src="/img/basket50x50.png" height="50" />';
	}

	public function becomeFoodsaver()
	{
		return '
	   <div class="msg-inside info">
			   <i class="fa fa-info-circle"></i> <strong><a href="/?page=settings&sub=upgrade/up_fs">Möchtest Du auch Lebensmittel bei Betrieben retten und fair-teilen?<br />Werde Foodsaver!</a></strong>
	   </div>';
	}

	public function u_nextDates($dates)
	{
		$out = '
		<div class="ui-padding">
			<ul class="datelist linklist">';
		foreach ($dates as $d) {
			$out .= '
				<li>
					<a href="/?page=fsbetrieb&id=' . $d['betrieb_id'] . '" class="ui-corner-all">
						<span class="title">' . $this->func->niceDate($d['date_ts']) . '</span>
						<span>' . $d['betrieb_name'] . '</span>
					</a>
				</li>';
		}
		$out .= '
			</ul>
		</div>';

		return $this->v_utils->v_field($out, $this->func->s('next_dates'));
	}

	public function u_myBetriebe($betriebe)
	{
		$out = '';
		if (!empty($betriebe['verantwortlich'])) {
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['verantwortlich'] as $b) {
				$list .= '
				<li>
					<a class="ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>
				</li>';
			}
			$list .= '
			</ul>';
			$out = $this->v_utils->v_field($list, 'Du bist verantwortlich für', array('class' => 'ui-padding'));
		}

		if (!empty($betriebe['team'])) {
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['team'] as $b) {
				$list .= '
				<li>
					<a class="ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>
				</li>';
			}
			$list .= '
			</ul>';
			$out .= $this->v_utils->v_field($list, 'Du holst Lebensmittel ab bei', array('class' => 'ui-padding'));
		}

		if (!empty($betriebe['waitspringer'])) {
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['waitspringer'] as $b) {
				$list .= '
				<li>
					<a class="ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>
				</li>';
			}
			$list .= '
			</ul>';
			$out .= $this->v_utils->v_field($list, 'Du bist auf der Springer- / oder Warteliste bei', array('class' => 'ui-padding'));
		}

		if (!empty($betriebe['anfrage'])) {
			$this->func->addJsFunc('
				function u_anfrage_action(key,el)
				{
					val = $(el).children("input:first").val().split(":::");
					
					if(key == "deny")
					{
						u_sign_out(val[0],val[1],el);
					}
					else if(key == "map")
					{
						u_gotoMap(val[0],val[1],el);
					}
				}
	
				function u_sign_out(fsid,bid,el)
					{
						var item = $(el);
						showLoader();
						$.ajax({
							dataType:"json",
							data: "fsid="+fsid+"&bid="+bid,
							url:"xhr.php?f=denyRequest",
							success : function(data){
								if(data.status == 1)
								{
									pulseSuccess(data.msg);
									window.setTimeout(function(){reload()},1500);
								}else{
									pulseError(data.msg);
									window.setTimeout(function(){reload()},1500);
								}
							},
							complete:function(){hideLoader();}
						});	
					}	
	
				function u_gotoMap(fsid,betriebid,el)
					{
						var item = $(el);
						showLoader();
						var baseUrl = "?page=map&bid=";
						window.location.href = baseUrl+betriebid;
						
					}	
			');
			$this->func->addJs('
				function createSignoutMenu() {
					return {
						callback: function(key, options) {
							u_anfrage_action(key,this);
						},
						items: {
							"deny": {name: "Austragen",icon:"delete"},
							"map":{name: "Auf Karte anschauen",icon:"accept"}
						}
					};
				}
			
				$("#store-request").on("click", function(e){
					var $this = $(this);
					$this.data("runCallbackThingie", createSignoutMenu);
					var _offset = $this.offset(),
						position = {
							x: _offset.left - 30, 
							y: _offset.top - 97
						}
					$this.contextMenu(position);
				});
	
				$.contextMenu({
					selector: "#store-request",
					trigger: "none",
					build: function($trigger, e) {
						return $trigger.data("runCallbackThingie")();
					}
				});		
				
				
			');
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['anfrage'] as $b) {
				//<a id="anfrage-betrieb" class="ui-corner-all" href="/?page=fsbetrieb&id='.$b['id'].'">'.$b['name'].'</a>
				$list .= '
				<li>
					<a id="store-request" class="ui-corner-all" href="#" onclick="return false;">' . $b['name'] . '<input type="hidden" name="anfrage" value="' . $this->func->fsId() . ':::' . $b['id'] . '" /></a>
				</li>';
			}
			$list .= '
			</ul>';
			$out .= $this->v_utils->v_field($list, 'Anfragen gestellt bei', array('class' => 'ui-padding'));
		}

		if (empty($out)) {
			$out = $this->v_utils->v_info('Du bist bis jetzt in keinem Filial-Team.');
		}

		if (S::may('bieb')) {
			$out .= '
				<div class="ui-widget ui-widget-content ui-corner-all margin-bottom ui-padding">
					<ul class="linklist">
						<li>
							<a href="/?page=betrieb&a=new" class="ui-corner-all">Neuen Betrieb eintragen</a>
						</li>
					</ul>
				</div>';
		}

		return $out;
	}

	public function u_updates($updates)
	{
		$out = '';
		$i = 0;
		foreach ($updates as $u) {
			$fs = array(
				'id' => $u['foodsaver_id'],
				'name' => $u['foodsaver_name'],
				'photo' => $u['foodsaver_photo'],
				'sleep_status' => $u['sleep_status']
			);
			$out .= '
			<div class="updatepost">
					<a class="poster ui-corner-all" href="#" onclick="profile(' . (int)$u['foodsaver_id'] . ');return false;">
						' . $this->func->avatar($fs, 50) . '
					</a>
					<div class="post">
						' . $this->u_update_type($u) . '
					</div>
					<div style="clear:both;"></div>
			</div>';
		}

		return $this->v_utils->v_field($out, $this->func->s('updates'), array('class' => 'ui-padding'));
	}

	public function u_update_type($u)
	{
		$out = '';
		if ($u['type'] == 'forum') {
			$out = '
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<a href="#" onclick="profile(' . (int)$u['foodsaver_id'] . ');return false;">' . $u['foodsaver_name'] . '</a> hat etwas zum Thema "<a href="/?page=bezirk&bid=' . $u['bezirk_id'] . '&sub=forum&tid=' . $u['id'] . '&pid=' . $u['last_post_id'] . '#post' . $u['last_post_id'] . '">' . $u['name'] . '</a>" ins Forum geschrieben.
						</div>
					</div>
	
					<div class="activity_feed_content_link">
						' . $u['post_body'] . '
					</div>
	
				</div>
				
				<div class="js_feed_comment_border">
					<div class="comment_mini_link_like">
						<div class="foot">
							<span class="time">' . $this->func->niceDate($u['update_time_ts']) . '</span>
						</div>
					</div>
					<div class="clear"></div>
				</div>';
		} elseif ($u['type'] == 'bforum') {
			$out = '
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<a href="#" onclick="profile(' . (int)$u['foodsaver_id'] . ');">' . $u['foodsaver_name'] . '</a> hat etwas zum Thema "<a href="/?page=bezirk&bid=' . $u['bezirk_id'] . '&sub=botforum&tid=' . $u['id'] . '&pid=' . $u['last_post_id'] . '#post' . $u['last_post_id'] . '">' . $u['name'] . '</a>" ins Botschafterforum geschrieben.
						</div>
					</div>
	
					<div class="activity_feed_content_link">
						' . $u['post_body'] . '
					</div>
	
				</div>
			
				<div class="js_feed_comment_border">
					<div class="comment_mini_link_like">
						<div class="foot">
							<span class="time">' . $this->func->niceDate($u['update_time_ts']) . '</span>
						</div>
					</div>
					<div class="clear"></div>
				</div>';
		} elseif ($u['type'] == 'bpin') {
			$out = '
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<a href="#" onclick="profile(' . (int)$u['foodsaver_id'] . ');">' . $u['foodsaver_name'] . '</a> hat etwas auf die Pinnwand von <a href="/?page=fsbetrieb&id=' . $u['betrieb_id'] . '">' . $u['betrieb_name'] . '</a> geschrieben.
						</div>
					</div>
	
					<div class="activity_feed_content_link">
						' . $u['text'] . '
					</div>
	
				</div>
			
				<div class="js_feed_comment_border">
					<div class="comment_mini_link_like">
						<div class="foot">
							<span class="time">' . $this->func->niceDate($u['update_time_ts']) . '</span>
						</div>
					</div>
					<div class="clear"></div>
				</div>';
		}

		return $out;
	}

	public function u_invites($invites)
	{
		$out = '';
		foreach ($invites as $i) {
			$out .= '
			<div class="post event" style="border-bottom:1px solid #E3DED3; padding-bottom:15px;">
				<a href="/?page=event&id=' . (int)$i['id'] . '" class="calendar">
					<span class="month">' . $this->func->s('month_' . (int)date('m', $i['start_ts'])) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>
						
				
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . (int)$i['id'] . '">' . $i['name'] . '</a></p>
							<p>' . $this->func->niceDate($i['start_ts']) . '</p>
						</div>
					</div>
	
					<div>
						<a href="#" onclick="ajreq(\'accept\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Einladung annehmen</a> <a href="#" onclick="ajreq(\'maybe\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Vielleicht</a> <a href="#" onclick="ajreq(\'noaccept\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Nein</a>
					</div>
				</div>
				
				<div class="clear"></div>
			</div>
			';
		}

		return $this->v_utils->v_field($out, 'Du wurdest eingeladen', array('class' => 'ui-padding'));
	}

	public function u_events($events)
	{
		$out = '';
		foreach ($events as $i) {
			$out .= '
			<div class="post event" style="border-bottom:1px solid #E3DED3; padding-bottom:15px;padding-top:15px;">
				<a href="/?page=event&id=' . (int)$i['id'] . '" class="calendar">
					<span class="month">' . $this->func->s('month_' . (int)date('m', $i['start_ts'])) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>
			
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . (int)$i['id'] . '">' . $i['name'] . '</a></p>
							<p>' . $this->func->niceDate($i['start_ts']) . '</p>
						</div>
					</div>
	
					<div>
						<a href="/?page=event&id=' . (int)$i['id'] . '" class="button">Zum Event</a> 
					</div>
				</div>
			
				<div class="clear"></div>
			</div>
			';
		}

		return $this->v_utils->v_field($out, 'Nächste Events', array('class' => 'ui-padding moreswap'));
	}
}
