<?php
/**
 * Created by IntelliJ IDEA.
 * User: matthias
 * Date: 18.02.18
 * Time: 00:45.
 */

namespace Foodsharing\Lib;

class Sanitizer
{
	public static function stripTagsAndTrim($v)
	{
		return trim(strip_tags($v));
	}

	public static function stripTagsAndTrimAllowBasicHtml($v)
	{
		return trim(strip_tags($v,
			'<p><ul><li><ol><strong><span><i><div><h1><h2><h3><h4><h5><br><img><table><thead><tbody><th><td><tr><i><a>'));
	}

	public static function isNonEmptyArray($v)
	{
		return is_array($v) && count($v);
	}

	public static function tagSelectIds($v)
	{
		$result = [];
		foreach ($v as $idKey => $value) {
			$result[] = explode('-', $idKey)[0];
		}

		return $result;
	}
}
