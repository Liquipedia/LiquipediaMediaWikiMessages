<?php

namespace Liquipedia\Extension\LiquipediaMediaWikiMessages\Api;

use ApiBase;
use Liquipedia\Extension\LiquipediaMediaWikiMessages\Cache;
use MediaWiki\MediaWikiServices;
use Wikimedia\ParamValidator\ParamValidator;

class UpdateMessageApiModule extends ApiBase {

	/**
	 *
	 */
	public function execute() {
		$messageName = $this->getRequest()->getText( 'messagename' );
		$value = $this->getRequest()->getText( 'value' );
		$services = MediaWikiServices::getInstance();
		$loadBalancer = $services->getDBLoadBalancer();

		$dbw = $loadBalancer->getConnection( DB_PRIMARY, '', $this->getConfig()->get( 'DBname' ) );
		$tablename = 'liquipedia_mediawiki_messages';

		// Search if the message is present
		$row = $dbw->selectRow( $tablename, [ '1' ], [ 'messagename' => $messageName ] );
		// If the message is not present, just return an error
		if ( !$row ) {
			$this->getResult()->addValue( null, $this->getModuleName(), [
				'message' => $this->msg( 'liquipediamediawikimessages-api-result-error' )
			] );
			return;
		}
		// Else update the message to the given value
		$dbw->update( $tablename, [ 'messagevalue' => $value ], [ 'messagename' => $messageName ] );

		// Delete from cache
		$cache = $services->getMainWANObjectCache();
		$cache->delete(
			$cache->makeGlobalKey(
				Cache::getPrefix(), md5( $messageName )
			),
		);

		$this->getResult()->addValue( null, $this->getModuleName(), [
			'message' => $this->msg( 'liquipediamediawikimessages-api-result-success' )
		] );
	}

	/**
	 * @return mixed
	 */
	public function getDescription() {
		return $this->msg( 'liquipediamediawikimessages-api-shortdesc' )->text();
	}

	/**
	 * @return mixed
	 */
	public function getParamDescription() {
		return parent::getParamDescription();
	}

	/**
	 * @return array
	 */
	public function getExamplesMessages() {
		return [
			'action=updatelpmwmessageapi&messagename=some_message_name&value=some_value&format=json'
			=> 'liquipediamediawikimessages-api-example',
		];
	}

	/**
	 * @return array
	 */
	public function getAllowedParams() {
		return [
			'messagename' => [
				ParamValidator::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'liquipediamediawikimessages-api-message-name',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'value' => [
				ParamValidator::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'liquipediamediawikimessages-api-message-value',
				ParamValidator::PARAM_REQUIRED => true,
			]
		];
	}
}
