<?php

namespace Liquipedia\Extension\LiquipediaMediaWikiMessages;

use MediaWiki\MediaWikiServices;

class Cache {

	/**
	 * @return string
	 */
	public static function getPrefix() {
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
		$cache = $mediaWikiServices->getMainWANObjectCache();
		$loadBalancer = $mediaWikiServices->getDBLoadBalancer();
		$result = $cache->getWithSetCallback(
			$cache->makeGlobalKey(
				self::getPrefix(), md5( $name )
			),
			$cache::TTL_DAY,
			static function () use ( $config, $name, $loadBalancer ) {
				$dbr = $loadBalancer->getConnection( DB_REPLICA, '', $config->get( 'DBname' ) );
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
