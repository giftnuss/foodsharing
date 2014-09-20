<?php 

class Bot
{
	private $html;
	private $cookie;
	private $curl;
	private $dom;	
	
	public function __construct()
	{
		$this->cookie = 'data/keks.cookie';
		
		if(!file_exists($this->cookie))
		{
			touch($this->cookie);
		}
		
		//file_put_contents('keks.txt','');
		$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
		
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		
		curl_setopt ($this->curl, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt ($this->curl, CURLOPT_COOKIEFILE, $this->cookie);
		
	}
	
	public function info($msg)
	{
		echo $msg."<br />\n";
	}
	
	public function checkOrGo($url)
	{
		if($this->getCurrentUrl() != $url)
		{
			$this->go($url);
		}
	}
	
	public function getDomRes($cssSelector)
	{
		$result = $this->dom->query($cssSelector);
		if(count($result) > 0)
		{
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	public function clearCookie()
	{
		file_put_contents($this->cookie,'');
	}
	
	public function close()
	{
		@curl_close($this->curl);
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	public function go($url)
	{
		curl_setopt($this->curl, CURLOPT_URL, $url);
		
		$this->execRequest();
	}
	
	private function execRequest()
	{
		$this->html = curl_exec($this->curl);
	} 
	
	public function submitForm($input,$option = false,$debug = false)
	{
		if($option == false)
		{
			$results = $this->dom->query('form');
		}
		elseif (isset($option['form_id']))
		{
			$results = $this->dom->query('#'.$option['form_id']);
		}
		elseif(isset($option['getInputs']))
		{
			$results = $this->dom->query($option['getInputs']);
		}
		
		if($debug)
		{
			//echo $this->html;
			//die();
			echo '<pre>';
			print_r($input);
			print_r($option);
		}
		if(count($results) > 0)
		{
			$submitData = array();
			foreach ($results as $r) 
			{
				if(!isset($option['method']))
				{
					$method = $r->getAttribute('method');
				}
				else
				{
					$method = $option['method'];
				}
			    if(!isset($option['action']))
			    {
			    	$action = $r->getAttribute('action');
			    }
			    else
			    {
			    	$action = $option['action'];
			    }
			    
			    
			    $method = trim($method);
			    $method = strtolower($method);
			    
			    if(empty($method))
			    {
			    	$method = 'get';
			    }
			    if(empty($action))
			    {
			    	$action = $this->getCurrentUrl();
			    }
			    
			    $inputs = $this->queryOnDomNode($r,'input');
			    foreach ($inputs as $in)
			    {
			    	if(!$in->hasAttribute('disabled'))
			    	{
			    		$submitData[$in->getAttribute('name')] = urlencode($in->getAttribute('value')); 
			    	}
			    }
			    
			    break;
			}
			
			foreach ($input as $key => $v)
			{
				$submitData[$key] = $v;
			}
			
			if($method == 'post')
			{
				curl_setopt($this->curl,CURLOPT_POST, count($submitData));
				curl_setopt($this->curl,CURLOPT_POSTFIELDS, $submitData);
			}
			else
			{
				$action .= '?';
				foreach ($submitData as $name => $value)
				{
					$action .= $name.'='.$value.'&';
				}
			}
			
			if(substr($action,04) != 'http')
			{
				$t = parse_url($this->getCurrentUrl());
								
				if(substr($action,0,1) == '/')
				{
					$action = '/'.$action;
					$action = $t['scheme'].'://'.$t['host'].$action;
				}
				else
				{
					$path = '';
					$part = explode('/',$t['path']);
					array_pop($part);
					
					foreach($part as $p)
					{
						if(!empty($p))
						{
							$path .= '/'.$p;
						}
					}
					
					$action = $t['scheme'].'://'.$t['host'].$path.'/'.$action;
				}
				
				
			}
			
			curl_setopt($this->curl, CURLOPT_URL, $action);
			
			if($debug)
			{
				echo $action."\n".$method."\n\n";
			}
			
			if($debug)
			{
				print_r($submitData);
				die('</pre>');
			}
			
			$this->execRequest();
			
			//$this->html;
		}
		else 
		{
			if($debug)
			{
				echo '=> Keine dom results..';
			}
		}
	}
	
	public function elementExists($cssSelector)
	{
		$res = $this->dom->query($cssSelector);
		
		if(count($res) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	public function getCurrentUrl()
	{
		return curl_getinfo($this->curl,CURLINFO_EFFECTIVE_URL);
	}
	
	public function getHtml()
	{
		return $this->html;
	}
	
	public function setHtml($html)
	{
		$this->html = $html;
		$this->dom = new Zend_Dom_Query($html);
	}
	
	function queryOnDomNode($node,$query)
	{
		$html = $this->getInnerHtml($node);
		$dom = new Zend_Dom_Query($html);
		return $dom->query($query);
	}
	
	function getInnerHtml( $node ) 
	{
		$innerHTML= '';
		$children = $node->childNodes;
		foreach ($children as $child) {
			$innerHTML .= $child->ownerDocument->saveXML( $child );
		}
	
		return $innerHTML;
	}
	
	public function cleanPhone($number)
	{
		$number = preg_replace('/[^0-9+]/','',$number);
		
		if(substr($number,0,3) == '+49')
		{
			$number = '+'.str_replace('+','',$number);
		}
		
		if(substr($number,0,4) == '0049')
		{
			$number = '+49'.substr($number,4);
		}
		
		if(substr($number,0,1) == '0')
		{
			$number = '+49'.substr($number,1);
		}
		
		return $number;
	}
}


?>