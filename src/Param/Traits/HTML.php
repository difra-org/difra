<?php

declare(strict_types=1);

namespace Difra\Param\Traits;

use Difra\Libs\Vault;

/**
 * Class HTML
 * @package Difra\Param\Traits
 */
trait HTML
{
    private bool $savedImages = false;
    private string $raw = '';

    /**
     * Verify
     * @param string $value
     * @return bool
     */
    public static function verify($value): bool
    {
        return (bool) trim($value);
    }

    /**
     * Save images
     * @param string $path
     * @param string $urlPrefix
     * @throws \Difra\DB\Exception|\Difra\Exception
     */
    public function saveImages(string $path, string $urlPrefix)
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
    public function val(bool $quiet = false): string
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
    function raw(): string
    {
        return $this->raw;
    }
}
