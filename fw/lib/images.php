<?php

namespace Difra;

final class Images {

	static function getInstance() {
		static $self = null;
		return $self ? $self : $self = new self;
	}

	/**
	 * Получение объекта из строки данных
	 * @param string|\Difra\Param\AjaxFile $data
	 * @return \Imagick|null
	 */
	private function data2image( $data ) {

		if( $data instanceof \Difra\Param\AjaxFile ) {
			$data = $data->val();
		}
		try {
			$img = new \Imagick;
			$img->readImageBlob( $data );
			return $img;
		} catch( Exception $ex ) {
			return null;
		}
	}

	/**
	 * Получение строки данных из объекта
	 * @param \Imagick $img
	 * @param string   $type
	 *
	 * @return string mixed
	 */
	private function image2data( $img, $type = 'png' ) {

		$img->setImageFormat( $type );
		if( $img->getImageWidth() * $img->getImageHeight() > 40000 ) {
			switch( $type ) {
			case 'png':
				$img->setImageInterlaceScheme( \imagick::INTERLACE_PNG );
				break;
			case 'jpeg':
				$img->setImageInterlaceScheme( \imagick::INTERLACE_JPEG );
				break;
			}
		}
		return $img->getImageBlob();
	}

	/**
	 * Перевод строки данных в другой формат
	 * @param string $data
	 * @param string $type
	 * @return bool|string
	 */
	public function convert( $data, $type = 'png' ) {

		$img = $this->data2image( $data );
		return $img ? $this->image2data( $img, $type ) : false;
	}

	/**
	 * Resizes image from binary string to given resolution keeping aspect ratio
	 *
	 * @param string|\Difra\Param\AjaxFile $data		binary string with image in it
	 * @param int                          $maxWidth        maximum height of thumbnail
	 * @param int                          $maxHeight       maximum width of thumbnail
	 * @param string                       $type		resulting image type
	 *
	 * @return string
	 */
	public function createThumbnail( $data, $maxWidth, $maxHeight, $type = 'png' ) {

		$img = $this->data2image( $data );
		$img->resizeImage( $maxWidth, $maxHeight, \Imagick::FILTER_LANCZOS, 0.9, true );
		return $this->image2data( $img, $type );
	}

	/**
	 * Resizes image from binary string to given resolution keeping aspect ratio
	 *
	 * @param string $data                    binary string with image in it
	 * @param int    $maxWidth		  maximum width of thumbnail
	 * @param int    $maxHeight               maximum height of thumbnail
	 * @param string $type                    resulting image type
	 *
	 * @return string
	 */
	public function scaleAndCrop( $data, $maxWidth, $maxHeight, $type = 'png' ) {

		$img = $this->data2image( $data );
		$img->cropThumbnailImage( $maxWidth, $maxHeight );
		return $this->image2data( $img, $type );
	}

}

