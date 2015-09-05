<?php

namespace Difra;

use Difra\Libs\XML\DOM;

/**
 * Class Plugger
 * @package Difra
 */
class Plugger
{
    /** @var \Difra\Plugin[] */
    private static $plugins = null;
    /** @var array */
    private static $pluginsData = null;
    /** @var array[] */
    private static $provisions = [];

    /**
     * Init
     */
    public static function init()
    {
        self::$provisions = [];
        self::$provisions['mysql'] =
                ['available' => MySQL::getInstance()->isConnected(), 'url' => '/test', 'source' => 'core'];
        self::smartPluginsEnable();
    }

    /**
     * Get all available plugins list
     * @return string[]
     */
    private static function getPluginsNames()
    {
        static $plugins = null;
        if (!is_null($plugins)) {
            return $plugins;
        }
        $plugins = [];
        if (!$plugins = Cache::getInstance()->get('plugger_plugins')) {
            if (is_dir(DIR_PLUGINS) and $dir = opendir(DIR_PLUGINS)) {
                while (false !== ($subdir = readdir($dir))) {
                    if ($subdir != '.' and $subdir != '..' and is_dir(DIR_PLUGINS . '/' . $subdir)) {
                        if (is_readable(DIR_PLUGINS . "/$subdir/plugin.php")) {
                            $plugins[] = $subdir;
                        }
                    }
                }
            }
            Cache::getInstance()->put('plugger_plugins', $plugins, 300);
        }
        return $plugins;
    }

    /**
     * Get array of all available plugins objects
     * @return \Difra\Plugin[]
     */
    public static function getAllPlugins()
    {
        if (!is_null(self::$plugins)) {
            return self::$plugins;
        }
        $plugins = [];
        $dirs = self::getPluginsNames();
        if (!empty($dirs)) {
            foreach ($dirs as $dir) {
                /** @noinspection PhpIncludeInspection */
                include(DIR_PLUGINS . '/' . $dir . '/plugin.php');
                $ucf = ucfirst($dir);
                $plugins[$dir] = call_user_func(["\\Difra\\Plugins\\$ucf\\Plugin", "getInstance"]);
            }
        }
        return self::$plugins = $plugins;
    }

    /**
     * Get all enabled plugins without those without required depencies
     */
    public static function smartPluginsEnable()
    {
        if (!is_null(self::$pluginsData)) {
            return;
        }
        self::$pluginsData = [];
        $plugins = self::getAllPlugins();
        if (empty($plugins)) {
            return;
        }
        $enabledPlugins = Config::getInstance()->get('plugins');
        if (!$enabledPlugins) {
            $enabledPlugins = [];
        }

        // составление списка плагинов
        foreach ($plugins as $name => $plugin) {
            $info = $plugin->getInfo();
            self::$pluginsData[$name] = [
                    'enabled' =>
                            in_array($name, $enabledPlugins, true) or
                            (isset($enabledPlugins[$name]) and $enabledPlugins[$name]),
                    'loaded' => false,
                    'require' => $info['requires'],
                    'provides' => $info['provides'],
                    'version' => $info['version'],
                    'description' => $info['description']
            ];
            $provs = array_merge([$name], $info['provides']);
            foreach ($provs as $prov) {
                if (isset(self::$provisions[$prov])) {
                    if (is_array(self::$provisions[$prov]['source'])) {
                        self::$provisions[$prov]['source'][] = $name;
                    } else {
                        self::$provisions[$prov]['source'] = [self::$provisions[$prov]['source'], $name];
                    }
                } else {
                    self::$provisions[$prov] = [
                            'available' => false,
                            'source' => $name
                    ];
                }
            }
        }
        // Load plugins
        do {
            $changed = false;
            foreach (self::$pluginsData as $name => $data) {
                if (!$data['enabled'] or $data['loaded']) {
                    // plugin is disabled or already loaded
                    continue;
                }
                // check if all provisions are available
                if (!empty($data['require'])) {
                    foreach ($data['require'] as $req) {
                        if (!self::$provisions[$req]['available']) {
                            continue 2;
                        }
                    }
                }
                // enable plugin
                self::$plugins[$name]->enable();
                self::$pluginsData[$name]['loaded'] = true;
                $changed = true;
                // set plugin provisions as available
                self::$provisions[$name]['available'] = true;
                foreach ($data['provides'] as $prov) {
                    self::$provisions[$prov]['available'] = true;
                }
            }
        } while ($changed);
        // Init plugins
        foreach (self::$plugins as $plugin) {
            if (!$plugin->isEnabled()) {
                continue;
            }
            $plugin->init();
        }
    }

    /**
     * Fill information about missing requirements, old versions, etc.
     */
    public static function fillMissingReq()
    {
        static $didIt = false;
        if ($didIt) {
            return;
        }
        $didIt = true;
        foreach (self::$pluginsData as $name => $data) {
            if (!$data['loaded'] and !empty($data['require'])) {
                foreach ($data['require'] as $req) {
                    if (!isset(self::$provisions[$req]) or !self::$provisions[$req]['available']) {
                        self::$pluginsData[$name]['missingReq'][] = $req;
                        self::$pluginsData[$name]['disabled'] = true;
                    }
                }
            }
            if ($data['version'] < (float)Envi\Version::VERSION) {
                self::$pluginsData[$name]['old'] = true;
            }
        }
    }

    /**
     * Get plugins information as XML
     * @param \DOMElement|\DOMNode $node
     */
    public static function getPluginsXML($node)
    {
        self::smartPluginsEnable();
        self::fillMissingReq();
        $pluginsNode = $node->appendChild($node->ownerDocument->createElement('plugins'));
        DOM::array2domAttr($pluginsNode, self::$pluginsData);
        $provisionsNode = $node->appendChild($node->ownerDocument->createElement('provisions'));
        DOM::array2domAttr($provisionsNode, self::$provisions);
    }

    /**
     * Get directories for all enabled plugins
     * @return array
     */
    public static function getPaths()
    {
        $paths = [];
        $plugins = self::getAllPlugins();
        if (empty($plugins)) {
            return [];
        }
        foreach ($plugins as $name => $plugin) {
            if ($plugin->isEnabled()) {
                $paths[$name] = $plugin->getPath() . '/';
            }
        }
        return $paths;
    }

    /**
     * Is plugin enabled?
     * @param string $pluginName
     * @return bool
     */
    public static function isEnabled($pluginName)
    {
        if (!isset(self::$plugins[$pluginName])) {
            return false;
        }
        return self::$plugins[$pluginName]->isEnabled();
    }

    /**
     * Enable plugin in configuration
     * @param string $name
     * @return bool
     */
    public static function turnOn($name)
    {
        if (!isset(self::$plugins[$name])) {
            return false;
        }
        $config = Config::getInstance();
        $conf = $config->get('plugins');
        if (!$conf) {
            $conf = [];
        }
        $conf[$name] = true;
        $config->set('plugins', $conf);
        return $config->save();
    }

    /**
     * Disable plugin in configuration
     * @param string $name
     * @return bool
     */
    public static function turnOff($name)
    {
        $config = Config::getInstance();
        $conf = $config->get('plugins');
        if (isset($conf[$name])) {
            unset($conf[$name]);
            $config->set('plugins', $conf);
        }
        if (false !== ($k = array_search($name, $conf))) {
            unset($conf[$k]);
            $config->set('plugins', $conf);
        }
        return $config->save();
    }
}
