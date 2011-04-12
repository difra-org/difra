<?php

final class Images {

	static function getInstance() {
		static $self = null;
		return $self ? $self : $self = new self;
	}

	/**
	 * Resizes image from binary string to given resolution keeping aspect ratio
	 *
	 * $data	binary string with image in it
	 * $maxHeight	maximum height of thumbnail
	 * $maxWidth	maximum width of thumbnail
	 * $type	resulting image type
	 */
	public function createThumbnail( $data, $maxWidth, $maxHeight, $type ) {
		$img = imagecreatefromstring( $data );
		$sizeX = imagesx( $img );
		$sizeY = imagesy( $img );
		if( ( $maxHeight >= $sizeY ) && ( $maxWidth >= $sizeX ) ) {
			$newX = $sizeX;
			$newY = $sizeY;
		} elseif( $sizeX / $maxWidth > $sizeY / $maxHeight ) {
			$newX = $maxWidth;
			$newY = round( $sizeY * $maxWidth / $sizeX );
		} else {
			$newY = $maxHeight;
			$newX = round( $sizeX * $maxHeight / $sizeY );
		}
		if( strtolower( $type ) == 'gif' ) {
			$newImg = imagecreate( $newX, $newY );
		} else {
			$newImg = imagecreatetruecolor( $newX, $newY );
		}
		imagecopyresampled( $newImg, $img,
				   0, 0, // destination x and y
				   0, 0, // source x and y
				   $newX, $newY, // destination width and height
				   $sizeX, $sizeY // source width and height
				   );
		$newData = $this->gdDataToFile( $newImg, $type );
		imagedestroy( $img );
		imagedestroy( $newImg );

		return $newData;
	}

	/**
	 * Resizes image from binary string to given resolution keeping aspect ratio
	 *
	 * $data	binary string with image in it
	 * $maxHeight	maximum height of thumbnail
	 * $maxWidth	maximum width of thumbnail
	 * $type	resulting image type
	 * $tobig	should we scale image to bigger if needed
	 */
	public function scaleAndCrop( $data, $maxWidth, $maxHeight, $type, $tobig = false ) {
		$img = imagecreatefromstring( $data );
		$sizeX = imagesx( $img );
		$sizeY = imagesy( $img );

		// scale if image is too big
		if( ( ( $maxHeight < $sizeY ) and ( $maxWidth < $sizeX ) ) or $tobig ) {
			if( $sizeX / $maxWidth < $sizeY / $maxHeight ) {
				$newX = $maxWidth;
				$newY = round( $sizeY * $maxWidth / $sizeX );
			} else {
				$newY = $maxHeight;
				$newX = round( $sizeX * $maxHeight / $sizeY );
			}
		} else {
			$newX = $sizeX;
			$newY = $sizeY;
		}

		// crop
		$sizeX1 = 0; $sizeX2 = $sizeX;
		$sizeY1 = 0; $sizeY2 = $sizeY;
		$newX1  = 0; $newX2  = $newX;
		$newY1  = 0; $newY2  = $newY;
		if( ( $newX2 - $newX1 ) > $maxWidth ) {
			$dA = ( $newX2 - $newX1 ) / $maxWidth;
			$dS = ( $sizeX2 - $sizeX1 ) / $dA / 2;
			$dM = ( $sizeX2 - $sizeX1 ) / 2;
			$sizeX1 = $dM - $dS;
			$sizeX2 = $dM + $dS;
			$newX1 = 0; $newX2 = $maxWidth;
		}
		if( ( $newY2 - $newY1 ) > $maxHeight ) {
			$dA = ( $newY2 - $newY1 ) / $maxHeight;
			$dS = ( $sizeY2 - $sizeY1 ) / $dA / 2;
			$dM = ( $sizeY2 - $sizeY1 ) / 2;
			$sizeY1 = $dM - $dS;
			$sizeY2 = $dM + $dS;
			$newY1 = 0; $newY2 = $maxHeight;
		}

		if( strtolower( $type ) == 'gif' ) {
			$newImg = imagecreate( $newX2 - $newX1, $newY2 - $newY1 );
		} else {
			$newImg = imagecreatetruecolor( $newX2 - $newX1, $newY2 - $newY1 );
		}
		imagecopyresampled( $newImg, $img,
				   $newX1, $newY1, // destination x and y
				   $sizeX1, $sizeY1, // source x and y
				   $newX2 - $newX1, $newY2 - $newY1, // destination width and height
				   $sizeX2 - $sizeX1, $sizeY2 - $sizeY1 // source width and height
				   );
		$newData = $this->gdDataToFile( $newImg, $type );
		imagedestroy( $img );
		imagedestroy( $newImg );

		return $newData;
	}

	private function gdDataToFile( $newImg, $type ) {
		ob_start();
		try {
			switch( strtolower( $type ) ) {
			case 'jpg':
			case 'jpeg':
				imagejpeg( $newImg );
				break;
			case 'gif':
				imagegif( $newImg );
				break;
			case 'png':
				imagepng( $newImg );
				break;
			default:
				error( "Unknown '$type' image type", __LINE__, __FILE__ );
			}
			$newData = ob_get_contents();
		} catch ( exception $ex ) {
			error( 'Exception: ' . $ex->message, __LINE__, __FILE__ );
		}

		@ob_end_clean();
		return $newData;
	}
}

