<?php

class SpecialLiquipediaMediaWikiMessages extends SpecialPage {
	
	function __construct() {
		parent::__construct( 'LiquipediaMediaWikiMessages', 'editinterface' );
	}

	function getGroupName() {
		return 'liquipedia';
	}

	function execute( $par ) {
		global $wgDBname;
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
			return;
		}
		$tablename = 'liquipedia_mediawiki_messages';
		$params = explode( '/', $par );
		$output = $this->getOutput();
		$output->addModules( 'ext.liquipediamediawikimessages.SpecialPage' );
		$this->setHeaders();
		$request = $this->getRequest();
		$dbw = wfGetDB( DB_MASTER, '', $wgDBname );
		if($params[0] == 'new') {
			$output->addWikiText( '<h2>' . $this->msg( 'liquipediamediawikimessages-add-new-message' )->text() . '</h2>' );
			$reqMessage = ucfirst( strtolower( $request->getText( 'reqmessage' ) ) );
			$reqValue = $request->getText( 'reqvalue' );
			if ( $request->getBool( 'createnew' ) ) {
				if( !empty( $reqMessage ) && !empty( $reqValue ) ) {
					try {
						$dbw->insert( 'liquipedia_mediawiki_messages', array( 'messagename' => $reqMessage, 'messagevalue' => $reqValue ) );
						$output->addWikiText( '<div class="alert alert-success">' . $this->msg( 'liquipediamediawikimessages-add-new-message-success' )->text() . '</div>' );
						$reqMessage = '';
						$reqValue = '';
					} catch( PDOException $e ) {
						if( $e->getCode() == 23000 ) {
							$output->addWikiText( '<div class="alert alert-danger">' . $this->msg( 'liquipediamediawikimessages-add-new-message-error-duplicate' )->text() . '</div>' );
						} else {
							$output->addWikiText( '<div class="alert alert-danger">' . $this->msg( 'liquipediamediawikimessages-add-new-message-error-unknown' )->text() . '</div>' );
						}
					}
				} else {
					$output->addWikiText( '<div class="alert alert-danger">' . $this->msg( 'liquipediamediawikimessages-add-new-message-empty' )->text() . '</div>' );
				}
			}
			$output->addHTML( '<form name="newliquipediamediawikimessagesmessage" method="post">
				<table>
					<tr>
						<td class="input-label"><label for="reqmessage">' . $this->msg( 'liquipediamediawikimessages-message' )->text() . '</label></td>
						<td class="input-container"><input type="text" name="reqmessage" id="reqmessage" value="' . $reqMessage . '"></td>
						<td class="input-helper">' . $this->msg( 'liquipediamediawikimessages-message-helper' )->text() . '</td>
					</tr>
					<tr>
						<td class="input-label"><label for="reqvalue">' . $this->msg( 'liquipediamediawikimessages-value' )->text() . '</label></td>
						<td class="input-container"><textarea rows="25" type="text" name="reqvalue" id="reqvalue">' . $reqValue . '</textarea></td>
						<td class="input-helper">' . $this->msg( 'liquipediamediawikimessages-value-helper' )->text() . '</td>
					</tr>
					<tr>
						<td> </td>
						<td colspan="2">
							<input type="submit" name="createnew" value="' . $this->msg( 'liquipediamediawikimessages-create-button' )->text() . '"> 
						</td>
					</tr>
				</table>
			</form>' );
		} elseif( ( $params[0] == 'edit' ) && isset( $params[1] ) && !empty( $params[1] ) ) {
			$output->addWikiText( '<h2>' . $this->msg( 'liquipediamediawikimessages-edit-message' )->text() . '</h2>' );
			$result = get_object_vars( $dbw->selectRow( 'liquipedia_mediawiki_messages', '*', array( 'id' => $params[1] ) ) );
			if( $result ) {
				$reqValue = $result['messagevalue'];
				if ( $request->getBool( 'editmessage' ) ) {
					$reqValue = $request->getText( 'reqvalue' );
					$dbw->update( 'liquipedia_mediawiki_messages', array( 'messagevalue' => $reqValue ), array( 'id' => $params[1] ) );
					$output->addWikiText( '<div class="alert alert-success">' . $this->msg( 'liquipediamediawikimessages-edit-message-success' )->text() . '</div>' );
				}
				$output->addHTML( '<form name="newliquipediamediawikimessagesmessage" method="post">
					<table>
						<tr>
							<td class="input-label"><label>' . $this->msg( 'liquipediamediawikimessages-message' )->text() . '</label></td>
							<td class="input-container"><code>' . htmlspecialchars( $result['messagename'] ) . '</code></td>
							<td class="input-helper"></td>
						</tr>
						<tr>
							<td class="input-label"><label for="reqvalue">' . $this->msg( 'liquipediamediawikimessages-value' )->text() . '</label></td>
							<td class="input-container"><textarea rows="25" name="reqvalue" id="reqvalue">' . $reqValue . '</textarea></td>
							<td class="input-helper">' . $this->msg( 'liquipediamediawikimessages-value-helper' )->text() . '</td>
						</tr>
						<tr>
							<td> </td>
							<td colspan="2">
								<input type="submit" name="editmessage" value="' . $this->msg( 'liquipediamediawikimessages-edit-button' )->text() . '"> 
							</td>
						</tr>
					</table>
				</form>' );
			} else {
				$output->addWikiText( '<div class="alert alert-warning">' . $this->msg( 'liquipediamediawikimessages-edit-message-nonexistent' )->text() . '</div>' );
			}
		} elseif( ( $params[0] == 'delete' ) && isset( $params[1] ) && !empty( $params[1] ) ) {
			$output->addWikiText( '<h2>' . $this->msg( 'liquipediamediawikimessages-delete-message' )->text() . '</h2>' );
			if ( $request->getBool( 'deletemessage' ) ) {
				$dbw->delete( 'liquipedia_mediawiki_messages', array( 'id' => $params[1] ) );
				$output->addWikiText( '<div class="alert alert-success">' . $this->msg( 'liquipediamediawikimessages-delete-message-success' )->text() . '</div>' );
			} else {
				$result = get_object_vars( $dbw->selectRow( 'liquipedia_mediawiki_messages', '*', array( 'id' => $params[1] ) ) );
				if( $result ) {
					$output->addWikiText( '<div class="alert alert-danger">' . $this->msg( 'liquipediamediawikimessages-delete-message-confirm' )->text() . '</div>' );
					$output->addWikiText( '<div>' . $this->msg( 'liquipediamediawikimessages-message' )->text() . ' <code>' . $result['messagename'] . '</code></div>' );
					$output->addWikiText( '<div>' . $this->msg( 'liquipediamediawikimessages-value' )->text() . ' <pre>' . $result['messagevalue'] . '</pre></div>' );
					$output->addWikiText( '<form name="deleteliquipediamediawikimessagesmessage" method="post"><input class="btn btn-danger" type="submit" name="deletemessage" value="' . $this->msg( 'liquipediamediawikimessages-delete-message-delete-button' )->text() . '"></form>' );
				} else {
					$output->addWikiText( '<div class="alert alert-warning">' . $this->msg( 'liquipediamediawikimessages-delete-message-nonexistent' )->text() . '</div>' );
				}
			}
		}
		$output->addWikiText( '<h2>' . $this->msg( 'liquipediamediawikimessages-all-messages' )->text() . '</h2>' );
		$output->addWikiText( '<div>[[Special:LiquipediaMediaWikiMessages/new|' . $this->msg( 'liquipediamediawikimessages-add-new-message-link' )->text() . ']]</div>' );
		$res = $dbw->select( 'liquipedia_mediawiki_messages', '*', '' );
		$table = '{| class="wikitable sortable"' . "\n";
		$table .= "|-\n!" . $this->msg( 'liquipediamediawikimessages-column-id' )->text() . "\n!" . $this->msg( 'liquipediamediawikimessages-column-name' )->text() . "\n!" . $this->msg( 'liquipediamediawikimessages-column-value' )->text() . "\n!" . $this->msg( 'liquipediamediawikimessages-column-edit' )->text() . "\n!" . $this->msg( 'liquipediamediawikimessages-column-delete' )->text() . "\n";
		while( $row = $res->fetchRow() ) {
			$table .= "|-\n|" . $row['id'] . "\n|" . $row['messagename'] . "\n|<pre>" . $row['messagevalue'] . "</pre>\n|[[Special:LiquipediaMediaWikiMessages/edit/" . $row['id'] . '|edit]]' . "\n|[[Special:LiquipediaMediaWikiMessages/delete/" . $row['id'] . '|delete]]' . "\n";
		}
		$table .= '|}';
		$output->addWikiText( $table );
	}
}