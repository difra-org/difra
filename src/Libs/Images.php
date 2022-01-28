<?php

declare(strict_types=1);

namespace Difra\Libs;

use Difra\Exception;
use Difra\Param\AjaxFile;

/**
 * Class Images
 * @package Difra\Libs
 */
final class Images
{
    /**
     * Forbid object creation
     */
    private function __construct()
    {
    }

    /**
     * Forbid object cloning
     */
    private function __clone()
    {
    }

    /**
     * Get image object from image data
     * @param string|AjaxFile $data
     * @return \Imagick|null
     *@throws Exception
     */
    public static function data2image(AjaxFile|string $data): ?\Imagick
    {
        if ($data instanceof AjaxFile) {
            $data = $data->val();
        } elseif ($data instanceof \Imagick) {
            return clone $data;
        }
        try {
            $img = new \Imagick();
            $img->readImageBlob($data);
            return $img;
        } catch (\ImagickException) {
            throw new Exception('Invalid image file format');
        }
    }

    /**
     * Get image data from image object
     * @param \Imagick $img
     * @param string $type
     * @return string
     * @throws \ImagickException
     */
    private static function image2data(\Imagick $img, string $type = 'png'): string
    {
        $img->setImageFormat($type);
        if ($img->getImageWidth() * $img->getImageHeight() > 40000) {
            switch ($type) {
                case 'png':
                    $img->setInterlaceScheme(\Imagick::INTERLACE_PNG);
                    break;
                case 'jpeg':
                    $img->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
                    break;
            }
        }
        return $img->getImageBlob();
    }

    /**
     * Convert image string between image formats
     * @param string $data
     * @param string $type
     * @return string|null
     * @throws \Difra\Exception
     * @throws \ImagickException
     */
    public static function convert(string $data, string $type = 'png'): ?string
    {
        $img = self::data2image($data);
        return $img ? self::image2data($img, $type) : null;
    }

    /**
     * Resize image from binary string to given resolution keeping aspect ratio
     * @param string|AjaxFile $data binary string with image in it
     * @param int $maxWidth maximum height of thumbnail
     * @param int $maxHeight maximum width of thumbnail
     * @param string $type resulting image type
     * @return string
     * @throws \Difra\Exception
     * @throws \ImagickException
     */
    public static function createThumbnail(AjaxFile|string $data, int $maxWidth, int $maxHeight, string $type = 'png'): string
    {
        $img = self::data2image($data);
        $width = $img->getImageWidth();
        $height = $img->getImageHeight();
        if ($maxWidth < $width or $maxHeight < $height) {
            if ($width / $maxWidth > $height / $maxHeight) {
                $nw = $maxWidth;
                $nh = round($height * $nw / $width);
            } else {
                $nh = $maxHeight;
                $nw = round($width * $nh / $height);
            }
            $img->resizeImage($nw, $nh, \Imagick::FILTER_LANCZOS, 0.9, false);
        }
        return self::image2data($img, $type);
    }

    /**
     * Resizes image from binary string to given resolution keeping aspect ratio
     * @param string $data binary string with image in it
     * @param int $maxWidth maximum width of thumbnail
     * @param int $maxHeight maximum height of thumbnail
     * @param string $type resulting image type
     * @return string
     * @throws \Difra\Exception
     * @throws \ImagickException
     */
    public static function scaleAndCrop(string $data, int $maxWidth, int $maxHeight, string $type = 'png'): string
    {
        $img = self::data2image($data);
        $img->cropThumbnailImage($maxWidth, $maxHeight);
        return self::image2data($img, $type);
    }

    /**
     * Add watermark (text or image)
     * @param string $image
     * @param string|null $text
     * @param string|null $watermarkImage
     * @param string $type
     * @param int $padding
     * @param float|int $opacity
     * @return string
     * @throws \Difra\Exception
     * @throws \ImagickException
     * @throws \ImagickDrawException
     * @throws \ImagickPixelException
     */
    public static function setWatermark(
        string $image,
        string $text = null,
        string $watermarkImage = null,
        string $type = 'png',
        int $padding = 0,
        float|int $opacity = 0.5
    ): string {
        if (is_null($text) && is_null($watermarkImage) || ($text != '' && $watermarkImage != '')) {
            return $image;
        }

        $originalImage = self::data2image($image);
        if (!is_null($watermarkImage) && $watermarkImage != '') {
            $watermarkImage = self::data2image($watermarkImage);
        }

        if (!is_null($text) && $text !== '') {
            // text watermark
            $watermarkImage = new \Imagick();

            $draw = new \ImagickDraw();
            // todo: need new solution
//            $draw->setFont(DIR_FW . 'lib/libs/capcha/DejaVuSans.ttf');
            $draw->setFontSize(10);
            $draw->setGravity(\Imagick::GRAVITY_CENTER);

            $textDArray = $watermarkImage->queryFontMetrics($draw, $text);
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
            return self::image2data($originalImage, $type);
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

        if (!is_null($text) && $text !== '') {
            $draw->setFillColor(new \ImagickPixel($textColor));
            $draw->setFillOpacity($opacity);
            $watermarkImage->annotateImage($draw, 0, 0, 0, $text);
        } else {
            $watermarkImage->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_ALPHA);
            //$watermarkImage->setImageOpacity( $opacity );
        }

        // Put watermark
        $originalImage->compositeImage($watermarkImage, \Imagick::COMPOSITE_OVER, $min[0], $min[1]);

        return self::image2data($originalImage, $type);
    }
}
