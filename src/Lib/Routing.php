<?php

namespace Foodsharing\Lib;

class Routing
{
	private static $classes = array('activity' => 'Activity',
		'api' => 'API',
		'application' => 'Application',
		'basket' => 'Basket',
		'bell' => 'Bell',
		'blog' => 'Blog',
		'buddy' => 'Buddy',
		'bcard' => 'BusinessCard',
		'content' => 'Content',
		'dashboard' => 'Dashboard',
		'event' => 'Event',
		'fairteiler' => 'FairTeiler',
		'foodsaver' => 'Foodsaver',
		'geoclean' => 'GeoClean',
		'index' => 'Index',
		'info' => 'Info',
		'login' => 'Login',
		'mailbox' => 'Mailbox',
		'main' => 'Main',
		'map' => 'Map',
		'msg' => 'Message',
		'newarea' => 'NewArea',
		'passport' => 'PassportGenerator',
		'profile' => 'Profile',
		'quiz' => 'Quiz',
		'bezirk' => 'Region',
		'report' => 'Report',
		'search' => 'Search',
		'settings' => 'Settings',
		'statistics' => 'Statistics',
		'betrieb' => 'Store',
		'team' => 'Team',
		'wallpost' => 'WallPost',
		'groups' => 'WorkGroup');

	public static $fqcnPrefix = '\\Foodsharing\\Modules\\';

	public static function getClassName($appName, $type = 'Xhr')
	{
		if (!array_key_exists($appName, self::$classes)) {
			return null;
		}

		return self::$fqcnPrefix . self::$classes[$appName] . '\\' . self::$classes[$appName] . $type;
	}
}
