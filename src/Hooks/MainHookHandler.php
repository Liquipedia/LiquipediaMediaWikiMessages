<?php

namespace Liquipedia\Extension\LiquipediaMediaWikiMessages\Hooks;

use Language;
use Liquipedia\Extension\LiquipediaMediaWikiMessages\Cache;
use MediaWiki\Cache\Hook\MessagesPreLoadHook;

class MainHookHandler implements
	MessagesPreLoadHook
{

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
		$languages = Language::getFallbacksFor( $code );
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
