<?php

class LiquipediaMediaWikiMessagesHooks {

	private static $messageCache = [];

	public static function onMessagesPreLoad( $title, &$message, $code ) {
		if ( $code === 'qqx' ) {
			return;
		}
		global $wgDBname;
		$bareTitle = str_replace( '/' . $code, '', $title );
		$languages = Language::getFallbacksFor( $code );
		array_unshift( $languages, $code );
		$dbr = wfGetDB( DB_REPLICA, '', $wgDBname );
		$found = false;
		for ( $i = 0; $i <= count( $languages ); $i++ ) {
			if ( $i < count( $languages ) ) {
				$usedTitle = $bareTitle . '/' . $languages[ $i ];
			} else {
				$usedTitle = $bareTitle;
			}
			if ( isset( self::$messageCache[ $usedTitle ] ) && self::$messageCache[ $usedTitle ] === false ) {
				return;
			} elseif ( isset( self::$messageCache[ $usedTitle ] ) ) {
				$message = self::$messageCache[ $usedTitle ];
				return;
			} else {
				$res = $dbr->select( 'liquipedia_mediawiki_messages', [ 'messagevalue' ], [ 'messagename' => $usedTitle ] );
				if ( $res->numRows() === 1 ) {
					$obj = $res->fetchObject();
					self::$messageCache[ $usedTitle ] = $obj->messagevalue;
					$message = $obj->messagevalue;
					return;
				}
				$res->free();
			}
		}
		if ( !$found ) {
			self::$messageCache[ $title ] = false;
		}
	}

}
