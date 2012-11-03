<?php

use Difra\Plugins, Difra\Plugins\Radio, Difra\Param;

class AdmRadioController extends Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function addinrotationAjaxAction( Param\AjaxString $channel, Param\AjaxData $inRotation = null ) {

		if( !Radio::getInstance()->addToChannel( $inRotation->val(), $channel->val() ) ) {
			$this->ajax->error( \Difra\Locales::getInstance()->getXPath( 'radio/errors/badAdd' ) );
		} else {
			$this->ajax->refresh();
		}
	}

	public function channelsAction() {

		$Radio        = \Difra\Plugins\Radio::getInstance();
		$channelsList = $Radio->getChannels();

		$this->root->appendChild( $this->xml->createElement( 'view-channels' ) );

		if( !empty( $channelsList ) ) {

			$channelsXml = $this->root->appendChild( $this->xml->createElement( 'channels' ) );

			foreach( $channelsList as $channelName ) {
				$Channel[$channelName] = $Radio->getChannel( $channelName );
				$Channel[$channelName]->getXML( $channelsXml );
			}
		}
	}

	public function channelsettingsAction( Param\AnyString $channelName ) {

		if( is_null( $channelName ) ) {
			$this->view->httpError( 404 );
		}

		$channelSettingsXml = $this->root->appendChild( $this->xml->createElement( 'settings-channel' ) );
		$Channel            = \Difra\Plugins\Radio::getInstance()->getChannel( $channelName );

		$Channel->getSettingsXML( $channelSettingsXml );
	}

	public function savechannelsettingsAjaxAction( Param\AnyString $channelName,
						       Param\AjaxString $name,
						       Param\AjaxString $mount,
						       Param\AjaxString $siteDescription = null,
						       Param\AjaxString $description = null,
						       Param\AjaxString $genre = null,
						       Param\AjaxString $url,
						       Param\AjaxInt $bitrate,
						       Param\AjaxInt $samplerate,
						       Param\AjaxString $hostname,
						       Param\AjaxInt $port,
						       Param\AjaxString $password,
						       Param\AjaxInt $reencode,
						       Param\AjaxInt $debug,
						       Param\AjaxInt $onLine,
						       Param\AjaxInt $minSongInQuery,
						       Param\AjaxInt $minArtistInQuery,
						       Param\AjaxInt $tracksCount ) {

		if( is_null( $channelName ) ) {
			$this->view->httpError( 404 );
		}

		$Channel = \Difra\Plugins\Radio::getInstance()->getChannel( $channelName->val() );

		$Locales = \Difra\Locales::getInstance();
		$Radio   = \Difra\Plugins\Radio::getInstance();
		if( !$Channel ) {
			$this->ajax->error( $Locales->getXPath( 'radio/errors/noChannel' ) );
			return;
		}

		$errors = false;
		// проверяем валидность битрейта
		if( !$Radio->checkBitrate( $bitrate->val() ) ) {
			$errors = true;
			$this->ajax->invalid( 'bitrate', $Locales->getXPath( 'radio/errors/invalidBitrate' ) );
		}

		// проверяем сэмплрейт
		if( !$Radio->checkSampleRate( $samplerate->val() ) ) {
			$errors = true;
			$this->ajax->invalid( 'samplerate', $Locales->getXPath( 'radio/errors/invalidSampleRate' ) );
		}

		if( mb_strlen( $password->val() ) <= 6 ) {
			$errors = true;
			$this->ajax->invalid( 'password', $Locales->getXPath( 'radio/errors/shortPassword' ) );
		}

		$dataArray = array(
			'name'           => $name->val(), 'mount' => $mount->val(), 'siteDescription' => $siteDescription->val(),
			'description'    => $description->val(), 'genre' => $genre->val(), 'url' => $url->val(), 'bitrate' => $bitrate->val(),
			'samplerate'     => $samplerate->val(), 'hostname' => $hostname->val(), 'port' => $port->val(),
			'password'       => $password->val(), 'reencode' => $reencode->val(), 'debug' => $debug->val(), 'onLine' => $onLine->val(),
			'minSongInQuery' => $minSongInQuery->val(), 'minArtistInQuery' => $minArtistInQuery->val(), 'tracksCount' => $tracksCount->val()
		);

		if( !$errors ) {
			$result = $Channel->setSettings( $channelName->val(), $dataArray );

			if( $result !== true ) {
				$this->ajax->error( $Locales->getXPath( 'radio/errors/' . $result ) );
			} else {

				$this->ajax->display( $Locales->getXPath( 'radio/channelSaved' )
						      . '<br/><br/><a class="button" onclick="ajaxer.close(this); switcher.page( \'/adm/radio/channels\' )">Закрыть</a>' );
			}
		}
	}

	public function playlistAction( Param\AnyString $channelName, Param\NamedString $sort = null ) {

		$sort = !is_null( $sort ) ? $sort->val() : null;

		$Channel = \Difra\Plugins\Radio::getInstance()->getChannel( $channelName->val() );
		if( !$Channel ) {
			$this->view->httpError( 404 );
		}
		/** @var \DOMElement $playListXml */
		$playListXml = $this->root->appendChild( $this->xml->createElement( 'playlist-view' ) );
		$Channel->getXML( $playListXml );
		/** @var \DOMElement $libraryXml */
		$libraryXml = $playListXml->appendChild( $this->xml->createElement( 'library' ) );
		$Channel->getLibraryXML( $libraryXml, $sort );

		$Channel->getPlayListXML( $playListXml );
		/** @var \DOMElement $artistHistoryXml */
		$artistHistoryXml = $playListXml->appendChild( $this->xml->createElement( 'artistHistory' ) );
		$Channel->getLastPlayedArtistsXML( $artistHistoryXml );
	}

	public function deletetrackAjaxAction( Param\AnyString $channelName, Param\AnyInt $trackId ) {

		if( !is_null( $channelName ) && !is_null( $trackId ) ) {

			$Channel = \Difra\Plugins\Radio::getInstance()->getChannel( $channelName->val() );
			$Channel->removeFromLibrary( $trackId->val() );
		}

		$this->ajax->refresh();
	}

	public function settracksettingsAjaxAction( Param\AnyString $channelName, Param\AnyInt $trackId, Param\AjaxString $weight ) {

		if( !is_null( $channelName ) && !is_null( $trackId ) && !is_null( $weight ) ) {

			$Channel = \Difra\Plugins\Radio::getInstance()->getChannel( $channelName->val() );
			$Channel->setTrackWeight( $trackId->val(), $weight->val() );
		}

		$this->ajax->refresh();
	}

	public function savelistAjaxAction( Param\AjaxString $songList, Param\AjaxString $channelName ) {

		if( !is_null( $songList ) ) {
			$order = json_decode( $songList->val() );
			if( !empty( $order ) ) {
				$newOrder = array();
				foreach( $order as $data ) {
					$nOrder = explode( '_', $data );
					if( isset( $nOrder[0] ) ) {
						$newOrder[] = $nOrder[0];
					}
				}

				$Channel = \Difra\Plugins\Radio::getInstance()->getChannel( $channelName->val() );
				$Channel->setPlayList( $newOrder );
			}
		}

		$this->ajax->refresh();
	}

	public function newchannelAction() {

		$this->root->appendChild( $this->xml->createElement( 'newchannel-view' ) );
	}

	public function createchannelAjaxAction( Param\AjaxString $name,
						 Param\AjaxString $mount,
						 Param\AjaxString $siteDescription = null,
						 Param\AjaxString $description = null,
						 Param\AjaxString $genre = null,
						 Param\AjaxString $url,
						 Param\AjaxInt $bitrate,
						 Param\AjaxInt $samplerate,
						 Param\AjaxString $hostname,
						 Param\AjaxInt $port,
						 Param\AjaxString $password,
						 Param\AjaxInt $reencode,
						 Param\AjaxInt $debug,
						 Param\AjaxInt $onLine,
						 Param\AjaxInt $minSongInQuery,
						 Param\AjaxInt $minArtistInQuery,
						 Param\AjaxInt $tracksCount ) {

		$errors  = false;
		$Radio   = \Difra\Plugins\Radio::getInstance();
		$Locales = \Difra\Locales::getInstance();
		// проверяем точку монтирования
		if( $Radio->checkMount( $mount->val() ) ) {
			$errors = true;
			$this->ajax->invalid( 'mount', $Locales->getXPath( 'radio/errors/dupMount' ) );
		}

		// проверяем валидность битрейта
		if( !$Radio->checkBitrate( $bitrate->val() ) ) {
			$errors = true;
			$this->ajax->invalid( 'bitrate', $Locales->getXPath( 'radio/errors/invalidBitrate' ) );
		}

		// проверяем сэмплрейт
		if( !$Radio->checkSampleRate( $samplerate->val() ) ) {
			$errors = true;
			$this->ajax->invalid( 'samplerate', $Locales->getXPath( 'radio/errors/invalidSampleRate' ) );
		}

		if( mb_strlen( $password->val() ) <= 6 ) {
			$errors = true;
			$this->ajax->invalid( 'password', $Locales->getXPath( 'radio/errors/shortPassword' ) );
		}

		$dataArray = array(
			'name'             => $name->val(), 'mount' => $mount->val(), 'siteDescription' => $siteDescription->val(),
			'description'      => $description->val(), 'genre' => $genre->val(), 'url' => $url->val(),
			'bitrate'          => $bitrate->val(), 'samplerate' => $samplerate->val(), 'hostname' => $hostname->val(),
			'port'             => $port->val(), 'password' => $password->val(), 'reencode' => $reencode->val(),
			'debug'            => $debug->val(), 'onLine' => $onLine->val(), 'minSongInQuery' => $minSongInQuery->val(),
			'minArtistInQuery' => $minArtistInQuery->val(), 'tracksCount' => $tracksCount->val()
		);

		if( !$errors ) {

			$result = $Radio->createChannel( $dataArray );
			if( $result !== true ) {
				$this->ajax->error( $Locales->getXPath( 'radio/errors/' . $result ) );
			} else {

				$this->ajax->display( $Locales->getXPath( 'radio/channelAdded' ) .
						      '<br/><br/><a class="button" onclick="ajaxer.close(this); switcher.page( \'/adm/radio/channels\' )">Закрыть</a>' );
			}
		}
	}

	public function channeldeleteAjaxAction( Param\AnyString $name ) {

		\Difra\Plugins\Radio::getInstance()->deleteChannel( $name->val() );
		$this->ajax->refresh();
	}
}
 
