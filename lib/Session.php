<?php
class S
{
	public static function init()
	{
		fSession::setPath('/tmp/lmr');
		fSession::setLength('24 hours', '1 week');
		//fSession::enablePersistence();
		
		fAuthorization::setAuthLevels(
			array(
				'admin' => 100,
				'orga' => 70,
				'bot' => 60,
				'bieb' => 45,
				'fs' => 40,
				'user' => 30,
				'user_unauth'  => 20,
				'presse' => 15,
				'guest' => 10
			)
		);
		
		fSession::open();
	}
	
	public static function setAuthLevel($role)
	{
		fAuthorization::setLoginPage('/?page=login');
		fAuthorization::setUserAuthLevel($role);
		fAuthorization::setUserACLs(
			array(
				'posts'  => array('*'),
				'users'  => array('add', 'edit', 'delete'),
				'groups' => array('add'),
				'*'      => array('list')
			)
		);
	}
	
	public static function logout()
	{
		S::set('user', false);
		fAuthorization::destroyUserInfo();
		S::setAuthLevel('guest');
	}
	
	public static function login($user)
	{
		if(isset($user['id']) && !empty($user['id']) && isset($user['rolle']))
		{			
			fAuthorization::setUserToken($user['id']);
			S::setAuthLevel(rolleWrapInt($user['rolle']));
			
			S::set('user', array(
				'name' => $user['name'],
				'nachname' => $user['nachname'],
				'photo' => $user['photo'],
				'bezirk_id' => $user['bezirk_id'],
				'email' => $user['email'],
				'rolle' => $user['rolle'],
				'token' => $user['token']
			));
			
			return true;
		}
		return false;
	}
	
	public static function user($index)
	{
		$user = S::get('user');
		return $user[$index];
	}
	
	public static function id()
	{
		return fAuthorization::getUserToken();
	}
	
	public static function may($role = 'user')
	{
		if (fAuthorization::checkAuthLevel($role)) {
			return true;
		}
		return false;
	}
	
	public static function getLocation()
	{
		$loc = fSession::get('g_location',false);
		if(!$loc)
		{
			$db = loadModel('basket');
			$loc = $db->getValues(array('lat','lon'),'foodsaver',fsId());
			S::set('g_location', $loc);
		}
		return $loc;
	}
	
	public static function destroy()
	{
		fSession::destroy();
	}
	
	public static function set($key,$value)
	{
		fSession::set($key, $value);
	}
	
	public static function get($var)
	{
		return fSession::get($var,false);
	}
	
	public static function addMsg($message,$type,$title = null)
	{
		$msg = fSession::get('g_message',array());
		
		if(!isset($msg[$type]))
		{
			$msg[$type] = array();
		}
		
		if(!$title)
		{
			$title = ' '.s($type);
		}
		else
		{
			$title = ' ';
		}
		
		$msg[$type][] = array('msg'=>$message,'title'=>$title);
		fSession::set('g_message', $msg);
	}
}