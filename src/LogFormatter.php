<?php

namespace Liquipedia\Extension\LiquipediaMediaWikiMessages;

class LogFormatter extends \LogFormatter {

	/**
	 * @return array
	 */
	public function getMessageParameters() {
		$params = parent::getMessageParameters();
		return $params;
	}

	/**
	 * @return string
	 */
	protected function getMessageKey() {
		$key = parent::getMessageKey();
		$params = $this->extractParameters();

		return $key;
	}

}
