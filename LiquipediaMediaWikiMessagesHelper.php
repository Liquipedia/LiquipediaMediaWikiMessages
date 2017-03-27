<?php

class LiquipediaMediaWikiMessagesHelper {
	public static function getDBO() {
		global $wgDBtype,
			$wgDBserver,
			$wgDBname,
			$wgDBuser,
			$wgDBpassword;
		$db = null;
		try {
			$db = new PDO( $wgDBtype . ':host=' . $wgDBserver. ';dbname=' . $wgDBname,
				$wgDBuser, $wgDBpassword );
			$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$db->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
			$db->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
		} catch( PDOException $e ) {
			// echo "Connection Error: " . $e->getMessage();
		}
		return $db;
	}

	public static function getTableName() {
		$tablename = 'liquipedia_mediawiki_messages';
		return $tablename;
	}
}

?>