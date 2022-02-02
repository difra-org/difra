<?php

declare(strict_types=1);

namespace Difra;

use Difra\Envi\Roots;

/**
 * Project configuration
 * Class Config
 * @package Difra
 */
class Config
{
    /** @var array|null Current configuration */
    private ?array $config = null;
    /** @var bool Modified flag */
    private bool $modified = false;

    /**
     * Singleton
     * @static
     * @return Config
     */
    public static function getInstance(): Config
    {
        static $instance = null;
        return $instance ?? $instance = new self();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->save();
    }

    /**
     * Save configuration
     */
    public function save(): void
    {
        if (!$this->modified) {
            return;
        }
        $diff = $this->diff();
        try {
            $db = DB::getInstance();
            $db->query('DELETE FROM `config`');
            $db->query('INSERT INTO `config` SET `config`=?', [serialize($diff)]);
            Cache::getInstance()->remove('config');
            $this->modified = false;
        } catch (\Difra\DB\Exception | Cache\Exception) {
        }
    }

    /**
     * Finds difference between saved configuration and current one
     * @return array
     */
    private function diff(): array
    {
        return $this->subDiff($this->loadFileConfigs(), $this->config);
    }

    /**
     * Recursive part for diff()
     * @param array $a1
     * @param array $a2
     * @return array
     */
    private function subDiff(array $a1, array $a2): array
    {
        if (empty($a2)) {
            return [];
        }
        $diff = [];
        foreach ($a2 as $key => $value) {
            if (!isset($a1[$key])) {
                $diff[$key] = $value;
            } elseif (is_array($value)) {
                if (!empty($this->subDiff($a1[$key], $value))) {
                    $diff[$key] = $value;
                }
            } elseif ($a1[$key] !== $value) {
                $diff[$key] = $value;
            }
        }
        foreach ($a1 as $key => $value) {
            if (!isset($a2[$key])) {
                $diff[$key] = null;
            }
        }
        return $diff;
    }

    /**
     * Get configuration from config.php
     * @return array
     */
    private function loadFileConfigs(): array
    {
        static $config = null;
        if (!is_null($config)) {
            return $config;
        }
        $config = [];
        foreach (Roots::get(Roots::FIRST_FW) as $root) {
            if (is_file($file = $root . '/config.php')) {
                $newConfig = include($file);
                $config = $this->merge($config, $newConfig);
            }
        }
        return $config;
    }

    /**
     * Merge two configuration arrays
     * @param array $a1
     * @param array $a2
     * @return array
     */
    private function merge(array $a1, array $a2): array
    {
        foreach ($a2 as $key => $value) {
            $a1[$key] = match ($key) {
                'instances' => array_merge($a1[$key] ?? [], $value),
                default => $value,
            };
        }
        return $a1;
    }

    /**
     * Get configuration item value
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed
    {
        $this->load();
        return $this->config[$key] ?? null;
    }

    /**
     * Load configuration
     */
    private function load()
    {
        if (!is_null($this->config)) {
            return;
        }
//        $cache = Cache::getInstance();
//        if ($c = $cache->get('config')) {
//            $this->config = $c;
//            return;
//        }
        $this->config = $this->loadFileConfigs();
        try {
            $conf = DB::getInstance()->fetchOne('SELECT `config` FROM `config` LIMIT 1');
            if ($conf) {
                $dynamicConfig = unserialize($conf);
                if (is_array($dynamicConfig)) {
                    $this->config = $this->merge($this->config, $dynamicConfig);
                }
            }
//            $cache->put('config', $this->config);
        } catch (DB\Exception) {
        }
    }

    /**
     * Set configuration item value
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value): void
    {
        $this->load();
        $this->config[$key] = $value;
        $this->modified = true;
    }

    /**
     * Get configuration array item value
     * @param string $key
     * @param string $arrayKey
     * @return mixed
     */
    public function getValue(string $key, string $arrayKey): mixed
    {
        $this->load();
        return $this->config[$key][$arrayKey] ?? null;
    }

    /**
     * Set configuration array item value
     * @param string $key
     * @param string $arrayKey
     * @param mixed $arrayValue
     * @throws \Difra\Exception
     */
    public function setValue(string $key, string $arrayKey, mixed $arrayValue): void
    {
        $this->load();
        if (!isset($this->config[$key])) {
            $this->config[$key] = [];
        } elseif (!is_array($this->config[$key])) {
            throw new \Difra\Exception('An attempt to set sub-item on non-array config value');
        }
        $this->config[$key][$arrayKey] = $arrayValue;
        $this->modified = true;
    }

    /**
     * Get full configuration
     * @return array|null
     */
    public function getConfig(): ?array
    {
        $this->load();
        return $this->config;
    }

    /**
     * Get modified configuration items
     * @return array
     */
    public function getDiff(): array
    {
        $this->load();
        return $this->diff();
    }

    /**
     * Reset configuration changes
     */
    public function reset()
    {
        $this->load();
        $this->config = [];
        $this->modified = true;
        $this->save();
    }
}
