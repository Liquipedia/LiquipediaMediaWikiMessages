<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

use MediaWiki\MediaWikiServices;

class Cache {

	/**
	 * @return string
	 */
	public function getPrefix() {
		return 'lpmm_';
	}

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
		$config = $mediaWikiServices->getMainConfig();
		$cache = $mediaWikiServices->getLocalServerObjectCache();
		$result = $cache->getWithSetCallback(
			$cache->makeKey(
				self::getPrefix(), md5( $name )
			),
			$config->get( 'MsgCacheExpiry' ),
			static function () use ( $config, $name ) {
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
