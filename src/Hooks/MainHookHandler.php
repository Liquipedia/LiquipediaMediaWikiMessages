<?php

namespace Liquipedia\Extension\LiquipediaMediaWikiMessages\Hooks;

use MediaWiki\Api\Hook\ApiCheckCanExecuteHook;
use MediaWiki\Cache\Hook\MessagesPreLoadHook;
use MediaWiki\Config\Config;
use MediaWiki\Languages\LanguageFallback;
use MediaWiki\Permissions\PermissionManager;
use Wikimedia\Rdbms\ILoadBalancer;

class MainHookHandler implements
	ApiCheckCanExecuteHook,
	MessagesPreLoadHook
{

	/**
	 * @var Config
	 */
	private Config $config;

	/**
	 * @var PermissionManager
	 */
	private PermissionManager $permissionManager;

	/**
	 * @var LanguageFallback
	 */
	private LanguageFallback $languageFallback;

	/**
	 * @var ILoadBalander
	 */
	private ILoadBalancer $loadBalancer;

	/**
	 * @param Config $config
	 * @param PermissionManager $permissionManager
	 * @param LanguageFallback $languageFallback
	 * @param ILoadBalancer $loadBalancer
	 */
	public function __construct(
		Config $config,
		PermissionManager $permissionManager,
		LanguageFallback $languageFallback,
		ILoadBalancer $loadBalancer
	) {
		$this->config = $config;
		$this->permissionManager = $permissionManager;
		$this->languageFallback = $languageFallback;
		$this->loadBalancer = $loadBalancer;
	}

	/**
	 * @param Module $module
	 * @param User $user
	 * @param Message &$message
	 *
	 * @return bool
	 */
	public function onApiCheckCanExecute( $module, $user, &$message ) {
		$moduleName = $module->getModuleName();
		$userRights = $this->permissionManager->getUserPermissions( $user );
		if (
			$moduleName === 'updatelpmwmessageapi' && !in_array( 'editinterface', $userRights )
		) {
			$message = 'updatelpmwmessageapi-action-notallowed';
			return false;
		}
		return true;
	}

	/**
	 * @param string $title
	 * @param string &$message
	 * @param string $code
	 */
	public function onMessagesPreLoad( $title, &$message, $code ) {
		if ( $code === 'qqx' ) {
			return;
		}
		$bareTitle = str_replace( '/' . $code, '', $title );
		$languages = $this->languageFallback->getAll( $code );
		array_unshift( $languages, $code );
		for ( $i = 0; $i <= count( $languages ); $i++ ) {
			if ( $i < count( $languages ) ) {
				$usedTitle = $bareTitle . '/' . $languages[ $i ];
			} else {
				$usedTitle = $bareTitle;
			}

			$dbr = $this->loadBalancer->getConnection( DB_REPLICA, '', $this->config->get( 'DBname' ) );
			$res = $dbr->select(
				'liquipedia_mediawiki_messages',
				[ 'messagevalue' ],
				[ 'messagename' => $usedTitle ]
			);
			$value = null;
			if ( $res->numRows() === 1 ) {
				$obj = $res->fetchObject();
				$value = $obj->messagevalue;
			}

			if ( $value ) {
				$message = $value;
				return;
			}
		}
	}

}
