<?php

namespace Liquipedia\Extension\LiquipediaMediaWikiMessages\Hooks;

use DatabaseUpdater;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;
use MediaWiki\MediaWikiServices;

class SchemaHookHandler implements
	LoadExtensionSchemaUpdatesHook
{

	/**
	 * @param DatabaseUpdater $updater
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$config = MediaWikiServices::getInstance()->getMainConfig();
		$dbName = $config->get( 'DBname' );
		$db = $updater->getDB();
		if ( !$db->tableExists( $dbName . '.liquipedia_mediawiki_messages', __METHOD__ ) ) {
			$updater->addExtensionTable( 'liquipedia_mediawiki_messages', __DIR__ . '/../../sql/liquipedia_mediawiki_messages.sql' );
		}
	}

}
