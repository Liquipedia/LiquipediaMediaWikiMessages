<?php

class LiquipediaMediaWikiMessagesHooks {
	public static function onMessagesPreLoad( $title, &$message ) {
		$tablename = LiquipediaMediaWikiMessagesHelper::getTableName();
		$dbo = LiquipediaMediaWikiMessagesHelper::getDBO();
		$sql = "SELECT * FROM `" . $tablename . "` WHERE `messagename` = :messagename";
		$pdostatement = $dbo->prepare( $sql );
		$pdostatement->execute( [':messagename' => $title] );
		$result = $pdostatement->fetch();
		if( $result ) {
			$message = $result['messagevalue'];
		}
	}
}