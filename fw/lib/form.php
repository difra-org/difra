<?php

class Form {
	public $resfile = false;
	public $instance = null;
	public $xml = null;
	public $formData = array(); // filled form data from forms

	static function getInstance( $resname, $resfile = false ) {

		static $_instances = array();
		if( !isset( $_instances[$resname] ) ) {
			$_instances[$resname] = new self( $resname, $resfile );
		}
		return $_instances[$resname];
	}

	public function __construct( $resname, $resfile = false ) {

		$this->instance = $resname;
		if( !$resfile ) {
			$resfile = $resname . '.xml';
		}
		if( is_file( DIR_SITE . "forms/$resfile" ) ) {
			$this->resfile = DIR_SITE . "forms/$resfile";
		} else {
			$dirs = Plugger::getInstance()->getFormDirs();
			foreach( $dirs as $dir ) {
				if( is_file( $dir . $resfile ) ) {
					$this->resfile = $dir . $resfile;
				}
			}
		}
		if( !$this->resfile or !file_exists( $this->resfile ) ) {
			throw new exception( 'Can\'t find form xml:' . $resfile );
		}
		$this->load();
	}

	public function load() {

		$this->xml = new DOMDocument( );
		$this->xml->load( $this->resfile );
		$this->xml->documentElement->setAttribute( 'name', $this->instance );
	}

	public function getForm( $withData = false ) {

		if( $withData ) {
			$this->_updateData( $this->xml->documentElement );
		}
		return $this->xml;
	}

	private function _updateData( DOMNode $node ) {

		if( isset( $this->formData[$node->getAttribute( 'name' )] ) ) {
			$node->setAttribute( 'value', $this->formData[$node->getAttribute( 'name' )] );
		}
		if( $node->hasChildNodes() ) {
			foreach( $node->childNodes as $next ) {
				if( $next->nodeType == XML_ELEMENT_NODE ) {
					$this->_updateData( $next );
				}
			}
		}
	}

	public function getFormXML( DOMNode $node, $withData = false ) {

		$node->appendChild( $node->ownerDocument->importNode( $this->getForm( true )->documentElement, true ) );
	}

	public function checkForm() {

		if( !isset( $_POST[$this->instance . '_submit'] ) ) {
			return false;
		}
		$errors = 0;
		foreach( $this->xml->documentElement->childNodes as $item ) {
			if( $item->nodeType != XML_ELEMENT_NODE ) {
				continue;
			}

			// проверяем заполненность элемента, если он необходим
			switch( $item->nodeName ) {
			case 'submit':
				$necessary = 0;
				break;
			default:
				$necessary = 1;
			}
			$necessary = $item->hasAttribute( 'necessary' ) ? $item->getAttribute( 'necessary' ) : $necessary;

			$name = $this->instance . '_' . $item->getAttribute( 'name' );
			if( $necessary and (
				( !isset( $_POST[$name] ) or trim( $_POST[$name] ) == '' or $_POST[$name] == array() )
				and
				( !isset( $_FILES[$name] ) or $_FILES[$name]['error'] != 0 )
			) ) {
				$item->setAttribute( 'error_required', '1' );
				$errors++;
				continue;
			}

			// проверяем, что в поле введены допустимые данные
			$name = $this->instance . '_' . ( $dataName = $item->getAttribute( 'name' ) );
			if( isset( $_POST[$name] ) ) {
				$value = $_POST[$name];
				if( !is_array( $value ) ) {
					$item->setAttribute( 'value', $value );
					$this->formData[$dataName] = $_POST[$name];
				} else {
					foreach( $value as $v ) {
						$valueNode = $item->appendChild( $item->ownerDocument->createElement( 'value' ) );
						$valueNode->setAttribute( 'key', $v );
						$this->formData[$dataName][] = $v;
					}
				}
				switch( $item->nodeName ) {
				case 'input':
					switch( $item->getAttribute( 'type' ) ) {
					case 'numeric':
						if( $value and ( !is_numeric( $value ) or $value < 0 ) ) {
							$item->setAttribute( 'error_type', '1' );
							$errors++;
						}
						break;
					case 'date':
						if( $value and !Locale::getInstance()->isDate( $value ) ) {
							$item->setAttribute( 'error_type_date', '1' );
							$errors++;
						}
						break;
					}
				}
			}
		}
		return $errors ? false : true;
	}

	public function getFormData() {

		return $this->formData;
	}

	public function putFormData( array $data ) {

		$this->formData = $this->formData + $data;
	}
}
