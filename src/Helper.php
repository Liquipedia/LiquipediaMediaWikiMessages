<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

use MediaWiki\MediaWikiServices;

class Helper {

	private static $cacheKeyPrefix = 'lp_mw_messages';

	/**
	 * @return string
	 */
	public static function getCacheKeyPrefix() {
		return self::$cacheKeyPrefix;
	}

	/**
	 * @return int
	 */
	public static function getCacheTTL() {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$ttl = $config->get( 'MsgCacheExpiry' );
		return $ttl;
	}

	private static $cacheDefaultValue = '<defaultvaluethatnooneshouldeverputin>';

	/**
	 * @return string
	 */
	public static function getCacheDefaultValue() {
		return self::$cacheDefaultValue;
	}

}
