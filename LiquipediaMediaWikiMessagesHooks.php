<?php

class LiquipediaMediaWikiMessagesHooks {

	private static $messageCache = array();

	public static function onMessagesPreLoad( $title, &$message, $code ) {
		global $wgDBname;
		$bareTitle = str_replace( '/' . $code, '', $title );
		$language = $code;
		$found = false;
		$last = false;
		$dbr = wfGetDB( DB_REPLICA, '', $wgDBname );
		while ( !$found ) {
			if ( !$last ) {
				$usedTitle = $bareTitle . '/' . $language;
			} else {
				$usedTitle = $bareTitle;
			}
			if ( isset( self::$messageCache[ $usedTitle ] ) && self::$messageCache[ $usedTitle ] === false ) {
				return;
			} elseif ( isset( self::$messageCache[ $usedTitle ] ) ) {
				$message = self::$messageCache[ $usedTitle ];
				$found = true;
			} else {
				$res = $dbr->select( 'liquipedia_mediawiki_messages', [ 'messagevalue' ], [ 'messagename' => $usedTitle ] );
				if ( $res->numRows() === 1 ) {
					$obj = $res->fetchObject();
					self::$messageCache[ $usedTitle ] = $obj->messagevalue;
					$message = $obj->messagevalue;
					$found = true;
				}
				$res->free();
				if ( $last ) {
					break;
				}
				$languages = Language::getFallbacksFor( $language );
				if ( count( $languages ) > 0 ) {
					$language = $languages[ 0 ];
				} else {
					$last = true;
				}
			}
		}
		if ( !$found ) {
			self::$messageCache[ $title ] = false;
		}
	}

}
