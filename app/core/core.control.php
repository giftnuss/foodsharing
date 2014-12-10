<?php
define('VAR_TYPE_STRING',1);
define('VAR_TYPE_INT',2);
define('VAR_TYPE_FLOAT',2);
class Control
{
	private $model;
	private $view;
	private $sub;
	private $sub_func;
	
	public function __construct()
	{
		$this->sub = false;
		$this->sub_func = false;
		if(isset($_GET['sub']))
		{
			$parts = explode('/', $_GET['sub']);
			foreach ($parts as $i => $p)
			{
				if(empty($p))
				{
					unset($parts[$i]);
				}
			}
			$sub = $parts[0];
			$sub_func = end($parts);
			
			if(method_exists($this, $sub) && method_exists($this, $sub_func))
			{
				$this->sub = $sub;
				$this->sub_func = $sub_func;
			}
		}
	}
	
	public function setTemplate($template)
	{
		global $g_template;
		$g_template = $template;
	}
	
	public function getSubFunc()
	{
		return $this->sub_func;
	}
	
	public function getSub()
	{
		return $this->sub;
	}
	
	public function setSub($sub,$func = false)
	{
		if($func === false)
		{
			$func = $sub;
		}
		$this->sub = $sub;
		$this->sub_func = $func;
	}
	
	public function getRequest($name,$type = VAR_TYPE_STRING)
	{
		if(isset($_REQUEST[$name]))
		{
			$val = $_REQUEST[$name];
			switch ($type)
			{
				case VAR_TYPE_STRING :
					return $this->getRequestString($val); break;
				case VAR_TYPE_INT :
					return $this->getRequestInt($val); break;
				case VAR_TYPE_FLOAT :
					return $this->getRequestFloat($val); break;
				default:
					return $this->getRequestString($val); break;
			}
		}
		else 
		{
			return false;
		}
	}
	
	public function getRequestString($val)
	{
		return $val;
	}
	public function getRequestInt($val)
	{
		return $val;
	}
	public function getRequestFloat($val)
	{
		return $val;
	}
	
	public function isLat($latitude){
    if (preg_match("/^-?([1-8]?[1-9]|[1-9]0)\.{1}\d{1,6}$/", $latitude)) {
        return true;
    } else {
        return false;
    }
  }
  
  public function isLon($longitude){
    if(preg_match("/^-?([1]?[1-7][1-9]|[1]?[1-8][0]|[1-9]?[0-9])\.{1}\d{1,6}$/",
      $longitude)) {
       return true;
    } else {
       return false;
    }
  }
  
	  public function wallpostsAfterPost($post_id)
	  {
	  	
	  }
  
  	public function wallposts($table,$id)
  	{
  		addJsFunc('
			function u_delPost(id)
  			{
  				var id = id;
  				$.ajax({
						url: "/xhrapp.php?app=wallpost&m=delpost&table='.$table.'&id='.$id.'&post=" + id,
						dataType: "JSON",
						success: function(data)
						{
							if(data.status == 1)
							{
								$(".wallpost-" + id).remove();
							}
						}
					});
  			}
  			function mb_finishImage(file)
  			{
  				$("#wallpost-attach").append(\'<input type="hidden" name="attach[]" value="image-\'+file+\'" />\');
  				$("#attach-preview div:last").remove();
  				$(".attach-load").remove();
  				$("#attach-preview").append(\'<a rel="wallpost-gallery" class="preview-thumb" href="images/wallpost/\'+file+\'"><img src="images/wallpost/thumb_\'+file+\'" height="60" /></a>\');
  				$("#attach-preview").append(\'<div style="clear:both;"></div>\');
  				$("#attach-preview a").fancybox();
  				mb_clear();
  			}
  			function mb_clear()
  			{
  				$("#wallpost-loader").html(\'\');
  				$("a.attach-load").remove();
  			}
  		');
  		addJs('
			//$("#wallpost-submit").hide();	
  			$("#wallpost-text").autosize(); 
			$("#wallpost-text").focus(function(){
				$("#wallpost-submit").show();	
			});	
  				
  			$("#wallpost-attach-trigger").change(function(){
  				$("#attach-preview div:last").remove();
  				$("#attach-preview").append(\'<a rel="wallpost-gallery" class="preview-thumb attach-load" href="#" onclick="return false;">&nbsp;</a>\');
  				$("#attach-preview").append(\'<div style="clear:both;"></div>\');
  				$("#wallpost-attachimage-form").submit();
  			});
  				
			$("#wallpost-text").blur(function(){
				setTimeout(function(){
					//$("#wallpost-submit").hide();	
				},1000);
				$("#wallpost-submit").show();	
			});
			$("#wallpost-post").submit(function(ev){
				ev.preventDefault();
				
			});
			$("#wallpost-attach-image").button().click(function(){
				$("#wallpost-attach-trigger").click();
			});
  			$("#wall-submit").button().click(function(ev){
  				ev.preventDefault();
  				if(($("#wallpost-text").val() != "" && $("#wallpost-text").val() != "'.s('write_teaser').'") || $("#attach-preview a").length > 0)
  				{  	
  					$(".wall-posts table tr:first").before(\'<tr><td colspan="2" class="load">&nbsp;</td></tr>\');	

  					attach = "";
  					$("#wallpost-attach input").each(function(){
  						attach = attach + ":" + $(this).val(); 
  					});
  					if(attach.length > 0)
  					{
  						attach = attach.substring(1);
  					}	
  					
  					text = $("#wallpost-text").val();
  					if(text == "'.s('write_teaser').'")
  					{
  						text = "";	
  					}
  						
	  				$.ajax({
						url: "/xhrapp.php?app=wallpost&m=post&table='.$table.'&id='.$id.'",
						type: "POST",
						data: {
	  						text: text,
							attach: attach
	  					},
						dataType: "JSON",
						success: function(data)
						{
							$("#wallpost-attach").html("");
							if(data.status == 1)
							{
								$(".wall-posts").html(data.html);
								$(".preview-thumb").fancybox();
								if(data.script != undefined)
								{
									$.globalEval(data.script);
								}
							}
						}
					});
					$("#wallpost-text").val("");
					$("#attach-preview").html("");
					$("#wallpost-attach").html("");
  					$("#wallpost-text")[0].focus();
  					$("#wallpost-text").css("height","33px");
				}
  			});
			$("#wallpost-attach-trigger").focus(function(){
  				$("#wall-submit")[0].focus();
  			});
			$.ajax({
					url: "/xhrapp.php?app=wallpost&m=update&table='.$table.'&id='.$id.'&last=0",
					dataType: "JSON",
					success: function(data)
					{
						if(data.status == 1)
						{
							$(".wall-posts").html(data.html);
							$(".preview-thumb").fancybox();
						}
					}
			});
			
		');
  		/*
  		 * setInterval(function(){
				last = 0;
				if($(".wall-posts input.pid").length > 0)
				{
					last = $(".wall-posts input.pid:first").val();
				}
				$.ajax({
					url: "xhrapp.php?app=wallpost&m=update&table='.$table.'&id='.$id.'&last=" + last,
					dataType: "JSON",
					success: function(data)
					{
						if(data.status == 1)
						{
							$(".wall-posts").html(data.html);
						}
					}
				});
			},5000);
  		 */
  		$posthtml = '';
  		
  		if(S::may())
  		{
  			$posthtml = '
  			<div class="tools ui-padding">
				<textarea id="wallpost-text" name="text" title="'.s('write_teaser').'" class="comment textarea inlabel"></textarea>
				<div id="attach-preview"></div>
				<div style="display:none;" id="wallpost-attach" /></div>	
					
				<div id="wallpost-submit" align="right">
					
					<span id="wallpost-loader"></span><span id="wallpost-attach-image"><i class="fa fa-image"></i> '.s('attach_image').'</span>
					<a href="#" id="wall-submit">'.s('send').'</a>
					<div style="overflow:hidden;height:1px;">
						<form id="wallpost-attachimage-form" action="xhrapp.php?app=wallpost&m=attachimage&table='.$table.'&id='.$id.'" method="post" enctype="multipart/form-data" target="wallpost-frame">
							<input id="wallpost-attach-trigger" type="file" maxlength="100000" size="chars" name="etattach" />
						</form>
					</div>
					
				</div>
				<div style="clear:both"></div>
				<div style="visibility:hidden;">
				<iframe name="wallpost-frame" src="empty.html" style="height:1px;" frameborder="0"></iframe>
				</div>
			</div>';
  		}
  		
		return '
		<div id="wallposts">
			'.$posthtml.'
			<div class="wall-posts">
				
			</div>
		</div>';
  	}
  	
  	public function isSubmitted($form = false)
  	{
  		if(isset($_POST) && !empty($_POST))
  		{
  			if($form !== false && $_POST['submitted'] != $form)
  			{
  				return false;
  			}
  			return true;
  		}
  		return false;
  	}
  	
  	public function getPostZip($zip)
  	{
  		if($zip = $this->getPost($zip))
  		{
  			if((int)$zip > 100)
  			{
  				return preg_replace('/[^0-9]/', '', $zip);
  			}
  		}
  		return false;
  	
  	}
  	
  	public function getPostHtml($name)
  	{
  		if($val = $this->getPost($name))
  		{
  			$val = strip_tags($val,'<p><ul><li><ol><strong><span><i><div><h1><h2><h3><h4><h5><br><img><table><thead><tbody><th><td><tr><i><a>');
  			$val = trim($val);
  			if(!empty($val))
  			{
  				return $val;
  			}
  		}
  		return false;
  	}
  	
  	public function getPostTags($name)
  	{
  		if($val = $this->getPost($name))
  		{
  			if($val = json_decode($val,true))
  			{
  				$out = array();
  				foreach ($val as $v)
  				{
  					$v = trim($v);
  					$v = strip_tags($v);
  					if(strlen($v) > 2)
  					{
  						$out[$v] = $v;
  					}
  				}
  				if(!empty($out))
  				{
  					$out2 = array();
  					foreach ($out as $o)
  					{
  						$out2[] = $o;
  					}
  					return $out2;
  				}
  			}
  		}
  	
  		return false;
  	}
  	
  	public function getPostDbDate($name)
  	{
  		if($ts = $this->getPostDate($name))
  		{
  			return date('Y-m-d H:i:s',$ts);
  		}
  		
  		return false;
  	}
  	
  	public function getPostDate($name)
  	{
  		if($date = $this->getPostString($name))
  		{
  			$date = explode(' ', $date);
  			$date = trim($date[0]);
  			if(!empty($date))
  			{
  				$date = explode('-', $date);
  				if(count($date) == 3 && (int)$date[0] > 0 && (int)$date[1] > 0 && (int)$date[2] > 0)
  				{
  					return mktime(0, 0, 0, (int)$date[1], (int)$date[2], (int)$date[0]);
  				}
  			}
  		}
  		return false;
  	}
  	
  	public function getPostTime($name)
  	{
  		if(isset($_POST[$name]['hour']) && isset($_POST[$name]['min']))
  		{
  			return array(
  				'hour' => (int)$_POST[$name]['hour'],
  				'min' => (int)$_POST[$name]['min']
  			);
  		}
  		return false;
  	}
  	
  	public function getPostString($name)
  	{
  		if($val = $this->getPost($name))
  		{
  			$val = strip_tags($val);
  			$val = trim($val);
  				
  			if(!empty($val))
  			{
  				return $val;
  			}
  		}
  		return false;
  	}
  	
  	public function getPostImages($name)
  	{
  		if(isset($_POST[$name]) && is_array($_POST[$name]))
  		{
  			$out = array();
  			foreach ($_POST[$name] as $image)
  			{
  				if(isset($_POST[$name.'-exists'][$image]))
  				{
  					$image = $_POST[$name.'-exists'][$image];
  					$image['exists'] = true;
  				}
  				else
  				{
  					$image = array
  					(
  							'file' => $image,
  							'exists' => false
  					);
  				}
  					
  				$out[] = $image;
  			}
  				
  			return $out;
  		}
  		return false;
  	}
  	
  	public function getPostFloat($name)
  	{
  		if($val = $this->getPost($name))
  		{
  			$val = trim($val);
  			return floatval($var);
  		}
  		return false;
  	}
  	
  	public function getPostInt($name)
  	{
  		if($val = $this->getPost($name))
  		{
  			$val = trim($val);
  			return (int)$val;
  		}
  		return false;
  	}
  	
  	public function getPost($name)
  	{
  		if(isset($_POST[$name]) && !empty($_POST[$name]))
  		{
  			return $_POST[$name];
  		}
  		return false;
  	}
  	
  	public function getPostRegEx($name,$pattern = '/[^a-z0-9A-Z]/')
  	{
  		if($val = $this->getPost($name))
  		{
  			$val = trim($val);
  			$val = preg_replace($pattern, '', $val);
  			if(!empty($val))
  			{
  				return $val;
  			}
  		}
  		return false;
  	}
  	
  	public function getPostFile($file)
  	{
  		if(isset($_FILES[$file]) && (int)$_FILES[$file]['size'] > 0)
  		{
  			return true;
  		}
  		return false;
  	}
  	
  	public function getPostLatLng($lat, $lng)
  	{
  		// later ;)
  		if(($lat = $this->getPost($lat)) && ($lng = $this->getPost($lng)))
  		{
  			return array(floatval($lat),floatval($lng));
  		}
  		return false;
  	}
  	
  	public function convMessage($recipient, $conversation_id, $msg, $tpl_id = 9)
  	{
  		/*
  		 * only send email if the user is not online
  		 */
  		
  		if(!Mem::userOnline($recipient['id']))
  		{
  			/*
  			 * only send email if the user want to retrieve emails
  			 */
  			if(Mem::user($recipient['id'], 'infomail'))
  			{
  				$sessdata = Mem::user(fsId(),'lastMailMessage');
  			
	  			if(!$sessdata)
	  			{
	  				$sessdata = array();
	  			}
	  			
	  			if(!isset($sessdata[$recipient['id']]) || (time() - $sessdata[$recipient['id']]) > 600)
	  			{
	  				$sessdata[$recipient['id']] = time();
	  				
	  				tplMail($tpl_id, $recipient['email'],array(
	  					'anrede' => genderWord($recipient['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
	  					'sender' => S::user('name'),
	  					'name' => $recipient['name'],
	  					'message' => $msg,
	  					'link' => BASE_URL.'?page=msg&uc='.(int)fsId().'cid='.(int)$conversation_id
	  				));
	  			}
	  			
	  			Mem::userSet(fsId(), 'lastMailMessage', $sessdata);
  			}
  		}
  	}
  	
  	public function mailMessage($sender_id,$recip_id, $msg, $tpl_id = 9)
  	{
  		$db = loadModel('mailbox');
  	
  		$info = $db->getVal('infomail_message', 'foodsaver', $recip_id);
  		if((int)$info > 0)
  		{
  			if(!isset($_SESSION['lastMailMessage']))
  			{
  				$_SESSION['lastMailMessage'] = array();
  			}
  			
  			if(!$db->isActive($recip_id))
  			{
  				if(!isset($_SESSION['lastMailMessage'][$recip_id]) || (time() - $_SESSION['lastMailMessage'][$recip_id]) > 600)
  				{
  					$_SESSION['lastMailMessage'][$recip_id] = time();
  					$foodsaver = $db->getOne_foodsaver($recip_id);
  					$sender = $db->getOne_foodsaver($sender_id);
  						
  					tplMail($tpl_id, $foodsaver['email'],array(
  							'anrede' => genderWord($foodsaver['geschlecht'], 'Lieber', 'Liebe', 'Liebe/r'),
  							'sender' => $sender['name'],
  							'name' => $foodsaver['name'],
  							'message' => $msg,
  							'link' => BASE_URL.'?page=msg&u2c='.(int)$sender_id
  					));
  				}
  	
  			}
  		}
  	}
  	
  	public function appout($data)
  	{
  		header('content-type: application/json; charset=utf-8');
  		if(isset($_GET['callback']) && strlen($_GET['callback']) > 1)
  		{
  			echo strip_tags($_GET['callback']) . '(' . json_encode($data) . ');';
  		}
  		exit();
  	}
  	
  	public function pushMessage($fs_id,$message,$title,$data = array(), $count = 1)
  	{
  		
  		/*
  		$db = loadModel('api');
  		if($ret = $db->getValues(array('gcm','iosid'),'foodsaver', $fs_id))
  		{
  			$messageData = array('message' => $message, 'title' => $title, 'msgcnt' => $count, 'd' => $data);
  			if($ret['gcm'] != '')
	  		{	  			
	  			return $this->sendGcmNotification(array($ret['gcm']),$messageData);
	  			
	  			//echo $response;
	  		}
	  		
	  		if($ret['iosid'] != '')
	  		{
	  			$data['message'] = $message;
	  			$this->sendIosNotification($ret['iosid'], $messageData,array( 'd' => $data ));
	  		}
  		}*/
  		
  	}
  	
  	public function sendIosNotification($deviceToken, $messageData, $data)
  	{
  		
  		include('lib/PushNotification.php');
  		
  		// set enovirnment and cretificate path
  		$push = new PushNotification('production','/var/www/lmr.de/ioscert/ck.pem');
  		// set device token
  		$push->setDeviceToken($deviceToken);
  		// Set pass phrase if any
  		$push->setPassPhrase('FcY9Rkvk');
  		// Set badge
  		$push->setBadge(1);
  		// Set message body
  		$push->setMessageBody($messageData['title']);
  		
  		$push->setData($data);
  		
  		$push->sendNotification();
  		
  	}
  	
  	public function sendGcmNotification( $registrationIdsArray, $messageData )
  	{
  		$apiKey = 'AIzaSyCgLZcUaDR17RMxq5JBdb-IPXWR0KhOf4o';
  		$headers = array("Content-Type:" . "application/json", "Authorization:" . "key=" . $apiKey);
  		$data = array(
  				'data' => $messageData,
  				'registration_ids' => $registrationIdsArray
  		);
  	
  		$ch = curl_init();
  	
  		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
  		curl_setopt( $ch, CURLOPT_URL, "https://android.googleapis.com/gcm/send" );
  		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
  		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
  		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
  		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($data) );
  	
  		$response = curl_exec($ch);
  		curl_close($ch);
  	
  		return $response;
  	}
  	
  	public function addBell($foodsaver_ids, $title, $body, $link_attributes = false)
  	{
  		if($link_attributes !== false)
  		{
  			$attr = serialize($link_attributes);
  		}
  		return $this->model->addBell($foodsaver_ids, $title, $body, $link_attributes);
  	}
  	
  	public function setContentWidth($left,$right)
  	{
  		global $content_left_width;
  		global $content_right_width;
  		$content_right_width = $right;
  		$content_left_width = $left;
  	}
  	
	public function uri($index) 
	{
		if (isset ( $_GET['uri'] )) 
		{
			$uri = explode('/',$_SERVER['REQUEST_URI']);
			if(isset($uri[$index]))
			{
				return $uri[$index];
			}
			
		}
		return false;
	}
	
	public function uriInt($index) 
	{
		if (($val = ( int ) $this->uri ( $index )) !== false) 
		{
			return $val;
		}
		return false;
	}
	
	public function uriStr($index) 
	{
		if (($val = $this->uri ( $index )) !== false) 
		{
			return preg_replace ( '/[^a-z0-9\-]/', '', $val );
		}
		return false;
	}
}