<?php

namespace Difra\Envi;

/**
 * Class Version
 *
 * Here was Subversion revision detection for automatic cache pruning on production updates.
 * No reason to delete this class because people who deploy from zip file will benefit from this anyways.
 * Additionally, this leaves us a way to auto-prune caches in production environments later.
 *
 * @package Difra\Envi
 */
class Version {

	/** Framework version */
	const VERSION = '6.0';
	/** Revision */
	const REVISION = 1;

	/**
	 * Get build
	 *
	 * @param bool $asArray
	 *
	 * @return array|string
	 */
	public static function getBuild( $asArray = false ) {

		static $revisionStr = null;
		static $revisionArr = null;

		if( is_null( $revisionArr ) ) {
			$revisionArr = [self::VERSION, self::REVISION];
		}

		if( $asArray ) {
			return $revisionArr;
		}

		if( !$revisionStr ) {
			$revisionStr = implode( '.', $revisionArr );
		}
		return $revisionStr;
	}
}