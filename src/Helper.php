<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

class Helper {

	private static $cacheKeyPrefix = 'liquipedia_mediawiki_messages';

	public static function getCacheKeyPrefix() {
		return self::$cacheKeyPrefix;
	}

	public static function getCacheTTL() {
		$config = \MediaWiki\MediaWikiServices::getInstance()->getMainConfig();
		$ttl = $config->get( 'MsgCacheExpiry' );
		return $ttl;
	}

	private static $cacheDefaultValue = '<defaultvaluethatnooneshouldeverputin>';

	public static function getCacheDefaultValue() {
		return self::$cacheDefaultValue;
	}

}
