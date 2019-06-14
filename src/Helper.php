<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

class Helper {

	private static $cacheKeyPrefix = 'liquipedia_mediawiki_messages';

	public static function getCacheKeyPrefix() {
		return self::$cacheKeyPrefix;
	}

	private static $cacheTTL = 86400;

	public static function getCacheTTL() {
		return self::$cacheTTL;
	}

	private static $cacheDefaultValue = '<defaultvaluethatnooneshouldeverputin>';

	public static function getCacheDefaultValue() {
		return self::$cacheDefaultValue;
	}

}
