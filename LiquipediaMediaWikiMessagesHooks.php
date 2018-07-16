<?php

class LiquipediaMediaWikiMessagesHooks {

	private static $messageCache = array();

	public static function onMessagesPreLoad( $title, &$message ) {
		global $wgDBname;
		if ( isset( self::$messageCache[ $title ] ) && self::$messageCache[ $title ] === false ) {
			return;
		} elseif ( isset( self::$messageCache[ $title ] ) ) {
			$message = self::$messageCache[ $title ];
		} else {
			$dbr = wfGetDB( DB_REPLICA, '', $wgDBname );
			$res = $dbr->select( 'liquipedia_mediawiki_messages', [ 'messagevalue' ], [ 'messagename' => $title ] );
			if ( $res->numRows() === 1 ) {
				$obj = $res->fetchObject();
				self::$messageCache[ $title ] = $obj->messagevalue;
				$message = $obj->messagevalue;
			} else {
				self::$messageCache[ $title ] = false;
			}
			$res->free();
		}
	}

}
