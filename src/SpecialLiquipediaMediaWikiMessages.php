<?php

namespace Liquipedia\LiquipediaMediaWikiMessages;

use HTMLForm;
use ManualLogEntry;
use SpecialPage;

class SpecialLiquipediaMediaWikiMessages extends SpecialPage {

	private $output;

	public function __construct() {
		parent::__construct( 'LiquipediaMediaWikiMessages', 'editinterface' );
	}

	/**
	 * @return string
	 */
	public function getGroupName() {
		return 'liquipedia';
	}

	/**
	 * @param string $par
	 */
	public function execute( $par ) {
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}
		$tablename = 'liquipedia_mediawiki_messages';
		$params = explode( '/', $par );
		$this->output = $this->getOutput();
		$this->output->addModules( 'ext.liquipediamediawikimessages.SpecialPage' );
		$this->setHeaders();
		$request = $this->getRequest();
		$dbw = wfGetDB( DB_MASTER, '', $this->getConfig()->get( 'DBname' ) );
		# $cacheKeyPrefix = Helper::getCacheKeyPrefix();
		# $cache = wfGetMessageCacheStorage();
		if ( $params[ 0 ] === 'new' ) {
			$this->addMessage();
		} elseif ( ( $params[ 0 ] === 'edit' ) && isset( $params[ 1 ] ) && !empty( $params[ 1 ] ) ) {
			$id = $params[ 1 ];
			$this->editMessage( $id );
		} elseif ( ( $params[ 0 ] === 'delete' ) && isset( $params[ 1 ] ) && !empty( $params[ 1 ] ) ) {
			$id = $params[ 1 ];
			$this->deleteMessage( $id );
		}
		$this->output->addWikiText(
			'<h2>' . $this->msg( 'liquipediamediawikimessages-all-messages' )->text() . '</h2>'
		);
		$this->output->addWikiText(
			'<div>[[Special:LiquipediaMediaWikiMessages/new|'
			. $this->msg( 'liquipediamediawikimessages-add-new-message-link' )->text()
			. ']]</div>'
		);
		$table = '{| class="wikitable sortable"' . "\n";
		$table .= "|-\n"
			. '!' . $this->msg( 'liquipediamediawikimessages-column-id' )->text() . "\n"
			. '!' . $this->msg( 'liquipediamediawikimessages-column-name' )->text() . "\n"
			. '!' . $this->msg( 'liquipediamediawikimessages-column-value' )->text() . "\n"
			. '!' . $this->msg( 'liquipediamediawikimessages-column-edit' )->text() . "\n"
			. '!' . $this->msg( 'liquipediamediawikimessages-column-delete' )->text() . "\n";
		$res = $dbw->select( $tablename, '*', '' );
		foreach ( $res as $row ) {
			$table .= "|-\n|"
				. $row->id . "\n|"
				. $row->messagename . "\n|"
				. '<pre>' . $row->messagevalue . "</pre>\n"
				. '|[[Special:LiquipediaMediaWikiMessages/edit/' . $row->id . '|edit]]' . "\n"
				. '|[[Special:LiquipediaMediaWikiMessages/delete/' . $row->id . '|delete]]' . "\n";
		}
		$table .= '|}';
		$this->output->addWikiText( $table );
	}

	/**
	 * Inserts the message into the database.
	 */
	private function addMessage() {
		$this->output->addWikiText(
			'<h2>' . $this->msg( 'liquipediamediawikimessages-add-new-message' )->text() . '</h2>'
		);
		$formDescriptor = [
			'MessageName' => [
				'type' => 'text',
				'label-message' => 'liquipediamediawikimessages-message',
				'size' => 20,
				'maxlength' => 255,
				'required' => true,
				'help-message' => 'liquipediamediawikimessages-message-helper',
			],
			'MessageValue' => [
				'type' => 'textarea',
				'label-message' => 'liquipediamediawikimessages-value',
				'rows' => 25,
				'required' => true,
				'help-message' => 'liquipediamediawikimessages-value-helper',
			],
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->setSubmitTextMsg( 'liquipediamediawikimessages-create-button' )
			->setSubmitCallback( [ $this, 'addMessageCB' ] )
			->show();
	}

	/**
	 * @param array $formData
	 */
	public function addMessageCB( $formData ) {
		if ( !empty( $formData[ 'MessageName' ] ) && !empty( $formData[ 'MessageValue' ] ) ) {
			$dbw = wfGetDB( DB_MASTER, '', $this->getConfig()->get( 'DBname' ) );
			$reqMessage = $formData[ 'MessageName' ];
			$reqValue = $formData[ 'MessageValue' ];
			$tablename = 'liquipedia_mediawiki_messages';
			# $cache->delete( $cache->makeGlobalKey( $cacheKeyPrefix, $reqMessage ) );
			try {
				$dbw->insert( $tablename, [ 'messagename' => $reqMessage, 'messagevalue' => $reqValue ] );
				$this->output->addWikiText(
					'<div class="alert alert-success">'
					. $this->msg( 'liquipediamediawikimessages-add-new-message-success' )->text()
					. '</div>'
				);

				$user = $this->getUser();
				$logEntry = new ManualLogEntry( 'liquipediamediawikimessages', 'added' );
				$logEntry->setPerformer( $user );
				$logEntry->setTarget( \Title::newFromText( 'LiquipediaMediaWikiMessages', NS_SPECIAL ) );
				$logEntry->setParameters( [ '4::Message Name' => $formData[ 'MessageName' ], '5::Action' => 'Added' ] );
				$logEntry->setComment( 'Added message: ' . $formData[ 'MessageName' ] );
				$logid = $logEntry->insert();
				$logEntry->publish( $logid );

				$reqMessage = '';
				$reqValue = '';
			} catch ( \Exception $e ) {
				if ( $e->getCode() == 23000 ) {
					$this->output->addWikiText(
						'<div class="alert alert-danger">'
						. $this->msg( 'liquipediamediawikimessages-add-new-message-error-duplicate' )->text()
						. '</div>' );
				} else {
					$this->output->addWikiText(
						'<div class="alert alert-danger">'
						. $this->msg( 'liquipediamediawikimessages-add-new-message-error-unknown' )->text()
						. '</div>' );
				}
			}
		} else {
			$this->output->addWikiText(
				'<div class="alert alert-danger">'
				. $this->msg( 'liquipediamediawikimessages-add-new-message-empty' )->text()
				. '</div>'
			);
		}
	}

	/**
	 * @param string $id
	 */
	private function editMessage( $id ) {
		$this->output->addWikiText(
			'<h2>' . $this->msg( 'liquipediamediawikimessages-edit-message' )->text() . '</h2>'
		);
		$dbw = wfGetDB( DB_MASTER, '', $this->getConfig()->get( 'DBname' ) );
		$tablename = 'liquipedia_mediawiki_messages';
		$res = $dbw->selectRow( $tablename, '*', [ 'id' => $id ] );
		if ( $res ) {
			$result = get_object_vars( $res );
			$formDescriptor = [
				'MessageId' => [
					'type' => 'hidden',
					'name' => 'hidden',
					'default' => $id,
				],
				'MessageName' => [
					'type' => 'text',
					'label-message' => 'liquipediamediawikimessages-message',
					'size' => 20,
					'maxlength' => 255,
					'required' => true,
					'default' => $result[ 'messagename' ],
					'readonly' => true,
				],
				'MessageValue' => [
					'type' => 'textarea',
					'label-message' => 'liquipediamediawikimessages-value',
					'rows' => 25,
					'help-message' => 'liquipediamediawikimessages-value-helper',
					'default' => $result[ 'messagevalue' ],
				],
			];

			$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
			$htmlForm
				->setSubmitTextMsg( 'liquipediamediawikimessages-edit-button' )
				->setSubmitCallback( [ $this, 'editMessageCB' ] )
				->show();
		} else {
			$this->output->addWikiText(
				'<div class="alert alert-warning">'
				. $this->msg( 'liquipediamediawikimessages-edit-message-nonexistent' )->text()
				. '</div>'
			);
		}
	}

	/**
	 * @param array $formData
	 */
	public function editMessageCB( $formData ) {
		$dbw = wfGetDB( DB_MASTER, '', $this->getConfig()->get( 'DBname' ) );
		$tablename = 'liquipedia_mediawiki_messages';
		$id = $formData[ 'MessageId' ];
		$message = $dbw->select( $tablename, '*', [ 'id' => $id ] )->fetchObject();
		if ( $message !== null ) {
			# $cache->delete( $cache->makeGlobalKey( $cacheKeyPrefix, trim( $message->messagename ) ) );
		}
		$reqValue = $formData[ 'MessageValue' ];
		$dbw->update( $tablename, [ 'messagevalue' => $reqValue ], [ 'id' => $id ] );
		$this->output->addWikiText(
			'<div class="alert alert-success">'
			. $this->msg( 'liquipediamediawikimessages-edit-message-success' )->text()
			. '</div>'
		);

		$user = $this->getUser();
		$logEntry = new ManualLogEntry( 'liquipediamediawikimessages', 'edited' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( \Title::newFromText( 'LiquipediaMediaWikiMessages', NS_SPECIAL ) );
		$logEntry->setParameters( [ '4::Message Name' => $formData[ 'MessageName' ], '5::Action' => 'Edited' ] );
		$logEntry->setComment( 'Edited message: ' . $formData[ 'MessageName' ] );
		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

	/**
	 * @param string $id
	 */
	private function deleteMessage( $id ) {
		$this->output->addWikiText(
			'<h2>' . $this->msg( 'liquipediamediawikimessages-delete-message' )->text() . '</h2>'
		);
		$tablename = 'liquipedia_mediawiki_messages';
		$dbw = wfGetDB( DB_MASTER, '', $this->getConfig()->get( 'DBname' ) );
		$res = $dbw->selectRow( $tablename, '*', [ 'id' => $id ] );
		if ( $res ) {
			$result = get_object_vars( $res );
			$this->output->addWikiText(
				'<div class="alert alert-danger">'
				. $this->msg( 'liquipediamediawikimessages-delete-message-confirm' )->text()
				. '</div>'
			);
			$formDescriptor = [
				'MessageId' => [
					'type' => 'hidden',
					'name' => 'hidden',
					'default' => $id,
				],
				'HiddenMessageName' => [
					'type' => 'hidden',
					'name' => 'hiddenname',
					'default' => $result[ 'messagename' ],
				],
				'MessageName' => [
					'type' => 'info',
					'label-message' => 'liquipediamediawikimessages-message',
					'default' => '<code>' . $result[ 'messagename' ] . '</code>',
					// If true, the above string won't be HTML escaped
					'raw' => true,
				],
				'MessageValue' => [
					'type' => 'info',
					'label-message' => 'liquipediamediawikimessages-value',
					'default' => '<pre>' . $result[ 'messagevalue' ] . '</pre>',
					// If true, the above string won't be HTML escaped
					'raw' => true,
				]
			];
			$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
			$htmlForm
				->setSubmitTextMsg( 'liquipediamediawikimessages-delete-message-delete-button' )
				->setSubmitCallback( [ $this, 'deleteMessageCB' ] )
				->show();
		} else {
			$this->output->addWikiText(
				'<div class="alert alert-warning">'
				. $this->msg( 'liquipediamediawikimessages-delete-message-nonexistent' )->text()
				. '</div>'
			);
		}
	}

	/**
	 * @param array $formData
	 */
	public function deleteMessageCB( $formData ) {
		$id = $formData[ 'MessageId' ];
		$tablename = 'liquipedia_mediawiki_messages';
		$dbw = wfGetDB( DB_MASTER, '', $this->getConfig()->get( 'DBname' ) );
		$message = $dbw->select( $tablename, '*', [ 'id' => $id ] )->fetchObject();
		if ( $message !== null ) {
			// $cache->delete( $cache->makeGlobalKey( $cacheKeyPrefix, trim( $message->messagename ) ) );
		}
		$dbw->delete( $tablename, [ 'id' => $id ] );
		$this->output->addWikiText(
			'<div class="alert alert-success">'
			. $this->msg( 'liquipediamediawikimessages-delete-message-success' )->text()
			. '</div>'
		);

		$user = $this->getUser();
		$logEntry = new ManualLogEntry( 'liquipediamediawikimessages', 'deleted' );
		$logEntry->setPerformer( $user );
		$logEntry->setTarget( \Title::newFromText( 'LiquipediaMediaWikiMessages', NS_SPECIAL ) );
		$logEntry->setParameters( [ '4::Message Name' => $formData[ 'HiddenMessageName' ], '5::Action' => 'Deleted' ] );
		$logEntry->setComment( 'Deleted message: ' . $formData[ 'HiddenMessageName' ] );
		$logid = $logEntry->insert();
		$logEntry->publish( $logid );
	}

}
