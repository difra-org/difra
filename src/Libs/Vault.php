<?php

declare(strict_types=1);

namespace Difra\Libs;

use Difra\DB;
use Difra\Envi;
use Difra\Envi\Session;

/**
 * Class Vault
 * Temporary files storage.
 * @package Difra\Libs
 */
class Vault
{
    /**
     * Add file to vault
     * @param string $data
     * @return int
     * @throws \Difra\DB\Exception
     * @throws \Difra\Exception
     */
    public static function add(string $data): int
    {
        $db = self::getDB();
        $db->query('DELETE FROM `vault` WHERE `created`<DATE_SUB(now(),INTERVAL 3 HOUR)');
        $db->query('INSERT INTO `vault` SET `data`=?', [$data]);
        if ($id = $db->getLastId()) {
            Session::start();
            if (!isset($_SESSION['vault'])) {
                $_SESSION['vault'] = [];
            }
            $_SESSION['vault'][$id] = 1;
        }
        return $id;
    }

    /**
     * Get file from vault
     * @param $id
     * @return string|null
     * @throws \Difra\Exception
     */
    public static function get($id): ?string
    {
        Session::start();
        if (!isset($_SESSION['vault']) or !isset($_SESSION['vault'][$id])) {
            return null;
        }
        return self::getDB()->fetchOne('SELECT `data` FROM `vault` WHERE `id`=?', [$id]);
    }

    /**
     * Delete file from vault
     * @param $id
     * @throws \Difra\DB\Exception|\Difra\Exception
     */
    public static function delete($id)
    {
        Session::start();
        if (!isset($_SESSION['vault'][$id])) {
            return;
        }
        unset($_SESSION['vault'][$id]);
        $db = self::getDB();
        $db->query('DELETE FROM `vault` WHERE `id`=?', [$id]);
        $db->query('DELETE FROM `vault` WHERE `created`<DATE_SUB(now(),INTERVAL 3 HOUR)');
    }

    /**
     * Save images
     * Saves images found in $html to $path and replaces paths in img src="..." to $urlPrefix/{$id}.png.
     * Warning: if $path contains files not found in $html's as img src="..." links, those files will be deleted.
     * 1. Use $path exclusively for one object.
     * 2. Call saveImages() before saving $html
     * @param $html
     * @param $path
     * @param $urlPrefix
     * @throws \Difra\DB\Exception|\Difra\Exception
     */
    public static function saveImages(&$html, $path, $urlPrefix)
    {
        // when using AjaxSafeHTML, characters inside src= are encoded using ESAPI
        $html =
            str_replace(
                'src="http&#x3a;&#x2f;&#x2f;' . Envi::getHost() . '&#x2f;up&#x2f;tmp&#x2f;',
                'src="/up/tmp/',
                $html
            );
        $html = str_replace('src="&#x2f;up&#x2f;tmp&#x2f;', 'src="/up/tmp/', $html);
        $html =
            str_replace(
                'src="http&#x3a;&#x2f;&#x2f;' . Envi::getHost() . str_replace('/', '&#x2f;', "$urlPrefix/"),
                'src="' . $urlPrefix . '/',
                $html
            );
        $html = str_replace('src="' . str_replace('/', '&#x2f;', $urlPrefix . '/'), 'src="' . $urlPrefix . '/', $html);

        preg_match_all('/src=\"\/up\/tmp\/([0-9]+)\"/', $html, $newImages);
        preg_match_all('/src=\"' . preg_quote($urlPrefix, '/') . '\/([0-9]+)\.png\"/', $html, $oldImages);
        if (!empty($oldImages[1])) {
            $usedImages = $oldImages[1];
        } else {
            $usedImages = [];
        }
        if (!empty($newImages[1])) {
            @mkdir($path, 0777, true);
            $urlPrefix = trim($urlPrefix, '/');
            foreach ($newImages[1] as $newImage) {
                $img = Vault::get($newImage);
                file_put_contents("$path/$newImage.png", $img);
                $html = str_replace("src=\"/up/tmp/$newImage\"", "src=\"/$urlPrefix/$newImage.png\"", $html);
                Vault::delete($newImage);
                $usedImages[] = $newImage;
            }
        }
        if (is_dir($path)) {
            $dir = opendir($path);
            while (false !== ($file = readdir($dir))) {
                if ($file[0] === '.') {
                    continue;
                }
                if (!str_ends_with($file, '.png') or !in_array(substr($file, 0, strlen($file) - 4), $usedImages)) {
                    @unlink("$path/$file");
                }
            }
        }
    }

    /**
     * @return DB\Adapters\Common
     * @throws \Difra\Exception
     */
    public static function getDB(): DB\Adapters\Common
    {
        return DB::getInstance('vault');
    }
}
