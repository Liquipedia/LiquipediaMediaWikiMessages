<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

use Language;

class Hooks {

	/**
	 * @param Title $title
	 * @param string &$message
	 * @param string $code
	 */
	public static function onMessagesPreLoad( $title, &$message, $code ) {
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
