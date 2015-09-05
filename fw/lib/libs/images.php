<?php

namespace Difra\Libs;

use Difra\Exception;
use Difra\Param\AjaxFile;

/**
 * Class Images
 * @package Difra\Libs
 */
final class Images
{
    // TODO: make class static
    /**
     * Singleton
     * @static
     * @return Images
     */
    public static function getInstance()
    {
        static $self = null;
        return $self ? $self : $self = new self;
    }

    /**
     * Get image object from image data
     * @param string|AjaxFile $data
     * @throws Exception
     * @return \Imagick|null
     */
    public function data2image($data)
    {
        if ($data instanceof AjaxFile) {
            $data = $data->val();
        } elseif ($data instanceof \Imagick) {
            return clone $data;
        }
        try {
            $img = new \Imagick;
            $img->readImageBlob($data);
            return $img;
        } catch (\ImagickException $ex) {
            throw new Exception('Invalid image file format');
        }
    }

    /**
     * Get image data from image object
     * @param \Imagick $img
     * @param string $type
     * @return string mixed
     */
    public function image2data($img, $type = 'png')
    {
        $img->setImageFormat($type);
        if ($img->getImageWidth() * $img->getImageHeight() > 40000) {
            switch ($type) {
                case 'png':
                    $img->setInterlaceScheme(\imagick::INTERLACE_PNG);
                    break;
                case 'jpeg':
                    $img->setInterlaceScheme(\imagick::INTERLACE_JPEG);
                    break;
            }
        }
        return $img->getImageBlob();
    }

    /**
     * Convert image string between image formats
     * @param string $data
     * @param string $type
     * @return bool|string
     */
    public function convert($data, $type = 'png')
    {
        $img = $this->data2image($data);
        return $img ? $this->image2data($img, $type) : false;
    }

    /**
     * Resizes image from binary string to given resolution keeping aspect ratio
     * @param string|AjaxFile $data binary string with image in it
     * @param int $maxWidth maximum height of thumbnail
     * @param int $maxHeight maximum width of thumbnail
     * @param string $type resulting image type
     * @return string
     */
    public function createThumbnail($data, $maxWidth, $maxHeight, $type = 'png')
    {
        $img = $this->data2image($data);
        $w = $img->getimagewidth();
        $h = $img->getimageheight();
        if ($maxWidth < $w or $maxHeight < $h) {
            if ($w / $maxWidth > $h / $maxHeight) {
                $nw = $maxWidth;
                $nh = round($h * $nw / $w);
            } else {
                $nh = $maxHeight;
                $nw = round($w * $nh / $h);
            }
            $img->resizeImage($nw, $nh, \Imagick::FILTER_LANCZOS, 0.9, false);
        }
        return $this->image2data($img, $type);
    }

    /**
     * Resizes image from binary string to given resolution keeping aspect ratio
     * @param string $data binary string with image in it
     * @param int $maxWidth maximum width of thumbnail
     * @param int $maxHeight maximum height of thumbnail
     * @param string $type resulting image type
     * @return string
     */
    public function scaleAndCrop($data, $maxWidth, $maxHeight, $type = 'png')
    {
        $img = $this->data2image($data);
        $img->cropThumbnailImage($maxWidth, $maxHeight);
        return $this->image2data($img, $type);
    }

    /**
     * Add watermark (text or image)
     * @param string $image
     * @param string $text
     * @param string $watermarkImage
     * @param string $type
     * @param int $padding
     * @param float|int $opacity
     * @return string
     */
    public function setWatermark(
        $image,
        $text = null,
        $watermarkImage = null,
        $type = 'png',
        $padding = 0,
        $opacity = 0.5
    ) {
        if (is_null($text) && is_null($watermarkImage) || ($text != '' && $watermarkImage != '')) {
            return $image;
        }

        $originalImage = $this->data2image($image);
        if (!is_null($watermarkImage) && $watermarkImage != '') {
            $watermarkImage = $this->data2image($watermarkImage);
        }

        if (!is_null($text) && $text != '') {

            // text watermark
            $watermarkImage = new \Imagick();

            $draw = new \ImagickDraw();
            $draw->setFont(DIR_FW . 'lib/libs/capcha/DejaVuSans.ttf');
            $draw->setFontSize(10);
            $draw->setGravity(\imagick::GRAVITY_CENTER);

            $textDArray = $watermarkImage->queryfontmetrics($draw, $text);
            $watermarkImage->newImage(
                $textDArray['textWidth'] + 7,
                $textDArray['textHeight'] + 1,
                new \ImagickPixel('none')
            );
            $watermarkImage->setImageFormat('png');
        }

        // create watermark

        $image_width = $originalImage->getImageWidth();
        $image_height = $originalImage->getImageHeight();
        $watermark_width = $watermarkImage->getImageWidth();
        $watermark_height = $watermarkImage->getImageHeight();

        // verify if watermark fits image

        if ($image_width < $watermark_width + $padding || $image_height < $watermark_height + $padding) {
            return $this->image2data($originalImage, $type);
        }

        // define watermark position

        $positions = [];
        $positions[] = [0 + $padding, 0 + $padding];
        $positions[] = [$image_width - $watermark_width - $padding, 0 + $padding];
        $positions[] = [$image_width - $watermark_width - $padding, $image_height - $watermark_height - $padding];
        $positions[] = [0 + $padding, $image_height - $watermark_height - $padding];

        $min = null;
        $min_colors = 0;
        $textColor = 'black';

        foreach ($positions as $position) {
            $colors =
                $originalImage->getImageRegion($watermark_width, $watermark_height, $position[0], $position[1])
                              ->getImageColors();

            if ($min === null || $colors <= $min_colors) {
                $min = $position;
                $min_colors = $colors;
            }
        }
        $region = $originalImage->getImageRegion($watermark_width, $watermark_height, $min[0], $min[1]);
        $region->scaleImage(1, 1);
        $aColor = $region->getImagePixelColor(1, 1)->getColor();
        $colorSum = $aColor['r'] + $aColor['g'] + $aColor['b'];
        if ($colorSum < 390) {
            $textColor = 'white';
        }

        if (!is_null($text) && $text != '') {
            $draw->setFillColor(new \ImagickPixel($textColor));
            $draw->setFillOpacity($opacity);
            $watermarkImage->annotateimage($draw, 0, 0, 0, $text);
        } else {
            $watermarkImage->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_ALPHA);
            //$watermarkImage->setImageOpacity( $opacity );
        }

        // Put watermark
        $originalImage->compositeImage($watermarkImage, \Imagick::COMPOSITE_OVER, $min[0], $min[1]);

        return $this->image2data($originalImage, $type);
    }
}
