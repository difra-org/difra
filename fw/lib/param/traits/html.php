<?php

namespace Difra\Param\Traits;

use Difra\Libs\Vault;

/**
 * Class HTML
 * @package Difra\Param\Traits
 */
trait HTML
{
    private $savedImages = false;
    private $raw = '';

    /**
     * Verify
     * @param string $value
     * @return string
     */
    public static function verify($value)
    {
        return trim($value);
    }

    /**
     * Save images
     * @param string $path
     * @param string $urlPrefix
     */
    public function saveImages($path, $urlPrefix)
    {
        Vault::saveImages($this->value, $path, $urlPrefix);
        $this->savedImages = true;
    }

    /**
     * Get safe html
     * Remember to call saveImages() first.
     * @param bool $quiet
     * @return string
     */
    public function val($quiet = false)
    {

        if (!$quiet and !$this->savedImages) {
            trigger_error(
                "HTML val() called before saveImages() and \$quiet parameter is not set. Is that really what you want?",
                E_USER_NOTICE
            );
        }
        return $this->value;
    }

    /**
     * Get raw html
     * @return string
     */
    function raw()
    {
        return $this->raw;
    }
}
