<?php

namespace Liquipedia\Extension\LiquipediaMediaWikiMessages\Hooks;

use Liquipedia\Extension\LiquipediaMediaWikiMessages\Cache;
use MediaWiki\Api\Hook\ApiCheckCanExecuteHook;
use MediaWiki\Cache\Hook\MessagesPreLoadHook;
use MediaWiki\Languages\LanguageFallback;
use MediaWiki\Permissions\PermissionManager;

class MainHookHandler implements
	ApiCheckCanExecuteHook,
	MessagesPreLoadHook
{

	/**
	 * @var PermissionManager
	 */
	private PermissionManager $permissionManager;

	/**
	 * @var LanguageFallback
	 */
	private LanguageFallback $languageFallback;

	/**
	 * @param PermissionManager $permissionManager
	 */
	public function __construct(
		PermissionManager $permissionManager,
		LanguageFallback $languageFallback
	) {
		$this->permissionManager = $permissionManager;
		$this->languageFallback = $languageFallback;
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
	 * @param Title $title
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
			$value = Cache::getByName( $usedTitle );
			if ( $value ) {
				$message = $value;
				return;
			}
		}
	}

}
