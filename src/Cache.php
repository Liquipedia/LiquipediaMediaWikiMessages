<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

use MediaWiki\MediaWikiServices;

class Cache {

	/**
	 * @var array
	 */
	private static $valueCache = [];

	/**
	 * @param string $name
	 * @return string|bool
	 */
	public static function getByName( $name ) {
		if ( array_key_exists( $name, self::$valueCache ) ) {
			return self::$valueCache[ $name ][ 'value' ];
		}
		$mediaWikiServices = MediaWikiServices::getInstance();
		$cache = $mediaWikiServices->getMainWANObjectCache();
		$config = $mediaWikiServices->getMainConfig();
		$result = $cache->getWithSetCallback(
			$cache->makeKey(
				'lpmm_', md5( $name )
			),
			$config->get( 'MsgCacheExpiry' ),
			function () use ( $config, $name ) {
			$dbr = wfGetDB( DB_REPLICA, '', $config->get( 'DBname' ) );
			$res = $dbr->select(
				'liquipedia_mediawiki_messages',
				[ 'messagevalue' ],
				[ 'messagename' => $name ]
			);
			if ( $res->numRows() === 1 ) {
				$obj = $res->fetchObject();
				return [ 'value' => $obj->messagevalue ];
			}
			$res->free();
			return [ 'value' => false ];
		}
		);
		self::$valueCache[ $name ] = $result;
		return $result[ 'value' ];
	}

}
