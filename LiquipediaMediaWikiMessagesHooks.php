<?php

class LiquipediaMediaWikiMessagesHooks {
	public static function onMessagesPreLoad( $title, &$message ) {
		global $wgDBname;
		$tablename = LiquipediaMediaWikiMessagesHelper::getTableName();
		try {
			$dbr = wfGetDB( DB_REPLICA, '', $wgDBname );
			$res = $dbr->select( $tablename, [ 'messagevalue' ], [ 'messagename' => $title ] );
			if( $res->numRows() === 1 ) {
				$obj = $res->fetchObject();
				$message = $obj->messagevalue;
			}
		} catch( Exception $e ) {
			//echo $e->getMessage();
		}
	}
}