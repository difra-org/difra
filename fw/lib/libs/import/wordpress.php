<?php

/**
 * @deprecated
 */
namespace Difra\Libs\Import;

class Wordpress {

	public static function importFile( $filename ) {

		$data = file_get_contents( $filename );
		$data = preg_replace( '/\x03/u', '', $data );
		$xml = new \DOMDocument( 1.1 );
		$xml->loadXML( $data );
		unset( $data );
		return self::import( $xml );
	}

	public static function importString( $xmlString ) {

		$xml = new \DOMDocument( 1.1 );
		$xml->loadXML( $xmlString );
		return self::import( $xml );
	}

	/**
	 * @param \DOMDocument $xml
	 *
	 * @return \Difra\Libs\Objects\Blog|null
	 * @throws \Difra\Exception
	 */
	public static function import( &$xml ) {

		$blog = new \Difra\Libs\Objects\Blog();
		if( !$rss = $xml->documentElement ) {
			throw new \Difra\Exception( 'Wordpress data is not XML' );
		}
		if( !$channels = $rss->getElementsByTagName( 'channel' ) ) {
			throw new \Difra\Exception( 'Can\'t find channel node in XML' );
		}
		if( $channels->length != 1 ) {
			throw new \Difra\Exception( 'Unexpected channels number in XML' );
		}
		foreach( $channels->item( 0 )->childNodes as $item ) {
			switch( $item->nodeName ) {
			case 'item':
				self::importItem( $blog, $item );
			}
		}
		return $blog;
	}

	/**
	 * @param \Difra\Libs\Objects\Blog $blog
	 * @param \DOMElement              $item
	 */
	public static function importItem( &$blog, &$item ) {

		$type = $item->getElementsByTagName( 'post_type' );
		if( !$type or !$type->length ) {
			return;
		}
		switch( $type->item( 0 )->nodeValue ) {
		case 'post':
			self::importPost( $blog, $item );
			break;
		case 'page':
			self::importPage( $blog, $item );
			break;
		case 'attachment':
			break;
		}
	}

	/**
	 * @param \Difra\Libs\Objects\Blog $blog
	 * @param \DOMElement              $item
	 *
	 * @throws \Difra\Exception
	 */
	public static function importPost( &$blog, &$item ) {

		$post = new \Difra\Libs\Objects\Post();
		/** @var \DOMElement $key */
		foreach( $item->childNodes as $key ) {
			if( $key->nodeType == XML_TEXT_NODE and !trim( $key->nodeValue ) ) {
				continue;
			}
			if( $key->hasChildNodes() ) {
				$sub = $key->childNodes->item( 0 );
				if( $sub->nodeType == XML_TEXT_NODE or $sub->nodeType == XML_CDATA_SECTION_NODE ) {
					$value = $sub->nodeValue;
				} else {
					$value = $key->nodeValue;
				}
			} else {
				$value = $key->nodeValue;
			}
			switch( $name = $key->nodeName ) {
			case 'title':
			case 'pubDate':
				$post->$name = $value;
				break;
			case 'dc:creator':
				$post->author = $value;
				break;
			case 'content:encoded':
				$post->body = $value;
				break;
			case 'category':
				$post->categories[] = $value;
				break;
			case 'wp:post_date':
				$post->date = $value;
				break;
			case 'wp:post_parent': // 0
				if( $value ) {
					throw new \Difra\Exception( 'wp:post_parent is not zero, i don\'t know how to handle that!' );
				}
				break;
			case 'description': // 0
				if( $value ) {
					throw new \Difra\Exception( 'post description is not empty, i don\'t know how to handle that!' );
				}
				break;
			case 'wp:comment':
				self::importComment( $post, $key );
				break;
			case 'wp:postmeta':
				$subKey = $subValue = '';
				foreach( $key->childNodes as $subNode ) {
					switch( $subNode->nodeName ) {
					case '#text':
						break;
					case 'wp:meta_key':
						$subKey = $subNode->nodeValue;
						break;
					case 'wp:meta_value':
						$subValue = $subNode->childNodes->item( 0 )->nodeValue;
						break;
					default:
						throw new \Difra\Exception( "Unknown field in wp:postmeta: '" . $subNode->nodeName . "'" );
					}
				}
				if( !$subKey or !$subValue ) {
					break;
				}
				$post->additionals[$subKey] = $subValue;
				break;
			case 'link': // http://blog.com/238-blog-title
				$post->oldLink = $value;
				break;
			case 'wp:post_type': // post
			case 'wp:post_id': // 238
			case 'guid': // http://blog.com/?p=238
			case 'wp:comment_status': // open
			case 'wp:ping_status': // open
			case 'wp:post_name': // encoded post title for URL
			case 'wp:status': // publish / draft
			case 'wp:post_date_gmt': // 2010-09-04 10:34:44
			case 'wp:is_sticky': // 0
			case 'wp:post_password': // empty
			case 'excerpt:encoded': // empty CDATA
			case 'wp:menu_order': // 0
				break;
			default:
				echo "Bad post element: $name (" . htmlspecialchars( $key->ownerDocument->saveXML( $key ) ) . ")<br/>";
			}
		}
		$blog->addPost( $post );
	}

	/**
	 * @param \Difra\Libs\Objects\Blog $blog
	 * @param \DOMElement              $item
	 */
	public static function importPage( &$blog, &$item ) {
		// TODO
	}

	public static function importComment( &$obj, $node ) {
		// TODO
	}
}