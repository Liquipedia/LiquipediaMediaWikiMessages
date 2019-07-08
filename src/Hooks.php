<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

use Language;

class Hooks {

	private static $messageCache = [];

	public static function onMessagesPreLoad( $title, &$message, $code ) {
		if ( $code === 'qqx' ) {
			return;
		}
		$cacheKeyPrefix = Helper::getCacheKeyPrefix();
		$cache = wfGetMessageCacheStorage();

		$bareTitle = str_replace( '/' . $code, '', $title );
		$languages = Language::getFallbacksFor( $code );
		array_unshift( $languages, $code );
		for ( $i = 0; $i <= count( $languages ); $i++ ) {
			if ( $i < count( $languages ) ) {
				$usedTitle = $bareTitle . '/' . $languages[ $i ];
			} else {
				$usedTitle = $bareTitle;
			}
			if ( array_key_exists( $usedTitle, self::$messageCache ) && self::$messageCache[ $usedTitle ] === Helper::getCacheDefaultValue() ) {
				return;
			} elseif ( array_key_exists( $usedTitle, self::$messageCache ) ) {
				$message = self::$messageCache[ $usedTitle ];
				return;
			} else {
				self::$messageCache[ $usedTitle ] = $cache->getWithSetCallback( $cache->makeGlobalKey( $cacheKeyPrefix, $usedTitle ), Helper::getCacheTTL(), function() use ( $usedTitle ) {
					$dbr = wfGetDB( DB_REPLICA, '', \MediaWiki\MediaWikiServices::getInstance()->getMainConfig()->get( 'DBname' ) );
					$res = $dbr->select( 'liquipedia_mediawiki_messages', [ 'messagevalue' ], [ 'messagename' => $usedTitle ] );
					if ( $res->numRows() === 1 ) {
						$obj = $res->fetchObject();
						$res->free();
						return $obj->messagevalue;
					}
					$res->free();
					return Helper::getCacheDefaultValue();
				} );
				self::$messageCache[ $title ] = self::$messageCache[ $usedTitle ];
				if ( self::$messageCache[ $usedTitle ] !== Helper::getCacheDefaultValue() ) {
					$message = self::$messageCache[ $usedTitle ];
					return;
				}
			}
		}
		self::$messageCache[ $title ] = Helper::getCacheDefaultValue();
	}

}
