<?php

namespace Difra\Param\Filters;

class HTML {

	/** @var array Список разрешенных тэгов, параметр — массив аттрибутов и обрабочиков */
	private $allowedTags = array(
		'a' => array( 'href' => 'cleanLink' ),
		'img' => array( 'src' => 'cleanLink' ),
		'br' => array(),
		'table' => array(),
		'tr' => array(),
		'td' => array( 'colspan' => 'cleanUnsignedInt', 'rowspan' => 'cleanUnsignedInt' ),
		'div' => array(),
		'em' => array(),
		'li' => array(),
		'ol' => array(),
		'p' => array(),
		'span' => array(),
		'strike' => array(),
		'u' => array(),
		'ul' => array(),
		'strong' => array(),
		'sub' => array(),
		'sup' => array(),
		'hr' => array()
	);

	/** @var array Стили, разрешенные для всех тэгов и соответствующие обработчики */
	private $allowedAttrsForAll = array(
		'style' => 'cleanStyles',
		'class' => 'cleanClasses'
	);

	/** @var array Список разрешенных стилей, значение — массив значений или true, если разрешено любое значение */
	private $allowedStyles = array(
		'font-weight' => array( 'bold', 'bolder', 'lighter', 'normal', '100', '200', '300', '400', '500', '600', '700', '800', '900' ),
		'text-align' => array( 'left', 'center', 'right', 'start', 'end' ),
		'color' => true,
		'text-decoration' => array( 'line-through', 'overline', 'underline', 'none' ),
		'font-style' => array( 'normal', 'italic', 'oblique' )
	);

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * @param string $source               Исходный HTML
	 * @param bool   $clean                Произвести ли чистку от говн
	 * @param bool   $withHeaders          Вернуть ли полную HTML-страницу (true) или только содержимое (false)
	 *
	 * @return string
	 */
	public function process( $source, $clean = true, $withHeaders = false ) {

		if( !trim( $source ) ) {
			return '';
		}

		// url decode
		try {
			$source = \Difra\Libs\ESAPI::encoder()->canonicalize( $source );
		} catch( \Exception $ex ) {
			return false;
		}

		// преобразование HTML в DOM
		$html = new \DOMDocument( '1.0' );
		libxml_use_internal_errors( true );
		$html->loadHTML( '<?xml version = "1.0" encoding = "utf-8"?>' . $source );
		libxml_use_internal_errors( false );
		$html->normalize();

		// чистка
		if( $clean ) {
			$bodyList = $html->documentElement->getElementsByTagName( 'body' );
			if( $bodyList->length and $bodyList->item( 0 )->childNodes->length ) {
				$body = $bodyList->item( 0 );
				$replaceNodes = array();
				foreach( $body->childNodes as $node ) {
					$newReplaceNodes = $this->clean( $node );
					$replaceNodes = array_merge( $replaceNodes, $newReplaceNodes );
				}
				if( !empty( $replaceNodes ) ) {
					foreach( $replaceNodes as $replaceNode ) {
						$this->replace( $replaceNode );
					}
				}
			}
		}

		// преобразование DOM в HTML
		if( $withHeaders ) {
			$output = $html->saveHTML();
		} else {
			$newDom = new \DOMDocument();
			foreach( $html->documentElement->childNodes as $node ) {
				if( $node->nodeName == 'body' ) {
					foreach( $node->childNodes as $subNode ) {
						$newDom->appendChild( $newDom->importNode( $subNode, true ) );
					}
				}
			}
			$output = $newDom->saveHTML();
		}
		return mb_convert_encoding( $output, 'UTF-8', 'HTML-ENTITIES' );
	}

	/**
	 * Чистка DOM-документа от недозволенного
	 *
	 * @param \DOMElement|\DOMNode $node
	 *
	 * @return \DOMElement[]
	 */
	private function clean( &$node ) {

		$replaceNodes = array();
		switch( $node->nodeType ) {
		case XML_ELEMENT_NODE:
			if( !isset( $this->allowedTags[$node->nodeName] ) ) {
				$replaceNodes[] = $node;
			}
			$this->cleanAttributes( $node, isset( $this->allowedTags[$node->nodeName] ) ? $this->allowedTags[$node->nodeName] : array() );
			break;
		case XML_TEXT_NODE:
			break;
		case XML_COMMENT_NODE:
			$replaceNodes[] = $node;
			break;
		case XML_TEXT_NODE:
			if( !trim( $node->nodeValue ) ) {
				$replaceNodes[] = $node;
			}
			break;
		default:
			$replaceNodes[] = $node;
		}
		if( $node->hasChildNodes() ) {
			foreach( $node->childNodes as $child ) {
				$newReplace = $this->clean( $child );
				$replaceNodes = array_merge( $newReplace, $replaceNodes );
			}
		}
		return $replaceNodes;
	}

	/**
	 * Чистка аттрибутов ноды по спискам допустимых аттрибутов
	 * @param \DOMElement $node
	 * @param array       $attributes
	 */
	private function cleanAttributes( &$node, $attributes = array() ) {

		if( ( $node instanceof \DOMElement or $node instanceof \DOMNode ) and $node->attributes->length ) {
			$delAttr = array();
			foreach( $node->attributes as $attr ) {
				if( isset( $attributes[$attr->name] ) ) {
					$filter = $attributes[$attr->name];
					$node->setAttribute( $attr->name, $this->$filter( $attr->value ) );
				} elseif( isset( $this->allowedAttrsForAll[$attr->name] ) ) {
					$filter = $this->allowedAttrsForAll[$attr->name];
					$node->setAttribute( $attr->name, $this->$filter( $attr->value ) );
				} else {
					$delAttr[] = $attr->name;
				}
			}
			foreach( $delAttr as $da ) {
				$node->removeAttribute( $da );
			}
		}
	}

	/**
	 * Замена элемента страницы на span, если он не пустой
	 *
	 * @param \DOMElement $node
	 */
	private function replace( &$node ) {

		if( !$node->hasChildNodes() ) {
			$node->parentNode->removeChild( $node );
			return;
		}
		\Difra\Libs\XML\DOM::renameNode( $node, 'span' );
	}

	/**
	 * Фильтр ссылок
	 * @param string $link
	 *
	 * @return string
	 */
	private function cleanLink( $link ) {

		if( \Difra\Libs\ESAPI::validateURL( $link ) ) {
			return \Difra\Libs\ESAPI::encoder()->encodeForHTMLAttribute( $link );
		}
		if( mb_substr( $link, 0, 1 ) == '/' ) {
			$newLink = 'http://' . \Difra\Site::getInstance()->getHostname() . $link;
			if( \Difra\Libs\ESAPI::validateURL( $newLink ) ) {
				return \Difra\Libs\ESAPI::encoder()->encodeForHTMLAttribute( $newLink );
			}
		}
		return '#';
	}

	/**
	 * Фильтр стилей
	 * @param string $attr
	 *
	 * @return string
	 */
	private function cleanStyles( $attr ) {

		$returnStyle = array();
		$stylesSet = explode( ';', $attr );
		foreach( $stylesSet as $value ) {
			$styleElements = explode( ':', $value, 2 );
			if( sizeof( $styleElements ) != 2 ) {
				continue;
			}

			$styleElements[0] = trim( $styleElements[0] );
			$styleElements[1] = trim( $styleElements[1] );

			// проверяем элемент
			if( array_key_exists( $styleElements[0], $this->allowedStyles ) ) {

				// проверяем значение
				if( $this->allowedStyles[$styleElements[0]] === true ) {
					$returnStyle[] = $styleElements[0] . ': ' . \Difra\Libs\ESAPI::encoder() . encodeForCSS( $styleElements[1] );
				} elseif( is_array( $this->allowedStyles[$styleElements[0]] )
					and in_array( $styleElements[1], $this->allowedStyles[$styleElements[0]] )
				) {
					$returnStyle[] = $styleElements[0] . ':' . $styleElements[1];
				}
			}
		}
		return implode( ';', $returnStyle );
	}

	/**
	 * Фильтр ссылок
	 * @param string $classes
	 *
	 * @return string
	 */
	private function cleanClasses( $classes ) {

		$newClasses = array();
		$cls = explode( ' ', $classes );
		foreach( $cls as $cl ) {
			// стили st-* используются для предзаданных стилей в редакторе
			if( ( mb_substr( $cl, 0, 3 ) == 'st-' ) and ( ctype_alnum( mb_substr( $cl, 3 ) ) ) ) {
				$newClasses[] = $cl;
			}
		}
		return implode( ' ', $newClasses );
	}

	/**
	 * Фильтр целых положительных чисел ≥1
	 * @param $input
	 *
	 * @return int|string
	 */
	private function cleanUnsignedInt( $input ) {

		$input = intval( $input );
		if( filter_var( $input, FILTER_VALIDATE_INT, array( 'options' => array( 'min_range' => 1 ) ) ) ) {
			return $input;
		} else {
			return '';
		}
	}
}
