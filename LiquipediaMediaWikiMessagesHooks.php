<?php

class LiquipediaMediaWikiMessagesHooks {
	public static function onMessagesPreLoad( $title, &$message ) {
		global $wgDBname;
		try {
			$dbr = wfGetDB( DB_REPLICA, '', $wgDBname );
			$res = $dbr->select( 'liquipedia_mediawiki_messages', [ 'messagevalue' ], [ 'messagename' => $title ] );
			if( $res->numRows() === 1 ) {
				$obj = $res->fetchObject();
				$message = $obj->messagevalue;
			}
			$res->free();
		} catch( Exception $e ) {
			//echo $e->getMessage();
		}
	}
}