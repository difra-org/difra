<?php

declare(strict_types=1);

namespace Difra\Envi;

use Difra\Cache;
use Difra\Controller;
use Difra\Debugger;
use Difra\Envi;
use Difra\Exception;
use Difra\Resourcer;
use Difra\View;
use Difra\View\HttpError;

/**
 * Class Action
 * Find controller and action for current URI
 * @package Difra
 */
class Action
{
    /** @var string[] */
    private static array $parameters = [];
    /** @var string|array|null */
    private static string|array|null $controllerClass = null;
    /** @var string */
    private static string $controllerFile = '';
    /** @var ?Controller */
    private static ?Controller $controllerObj = null;
    /** @var ?string */
    private static ?string $controllerUri = null;
    /** @var ?string */
    private static ?string $actionUri = null;
    /** @var ?string */
    public static ?string $method = null;
    /** @var ?string */
    public static ?string $methodAuth = null;
    /** @var ?string */
    public static ?string $methodAjax = null;
    /** @var ?string */
    public static ?string $methodAjaxAuth = null;
    /** @var array[] */
    public static array $methodTypes = [
        ['', ''],
        ['', 'Auth'],
        ['Ajax', ''],
        ['Ajax', 'Auth']
    ];

    /**
     * Find controller and action for current URI
     * @throws Exception
     * @throws HttpError
     */
    public static function find()
    {
        if (self::loadCache()) {
            Debugger::addLine('Cached controller ' . self::$controllerClass . ' from ' . self::$controllerFile);
            return;
        }

        $uri = trim(Envi::getUri(), '/');
        $parts = $uri ? explode('/', $uri) : [];

        self::getResource($parts);

        $uriParts = $parts;
        if (!self::$controllerFile = self::findController($parts)) {
            self::saveCache('404');
            if (!Debugger::isEnabled()) {
                throw new HttpError(404);
            } else {
                throw new HttpError('Failed to find controller', 404);
            }
        }
        self::$controllerUri = self::$actionUri = '/' . implode(
                '/',
                sizeof($parts) ? array_slice($uriParts, 0, -sizeof($parts)) : $uriParts
            );

        include_once(self::$controllerFile);
        if (!class_exists(self::$controllerClass)) {
            throw new Exception('Error! Controller class ' . self::$controllerClass . ' not found');
        }

        self::findAction($parts);
        self::$parameters = $parts;

        self::saveCache();
        Debugger::addLine('Selected controller ' . self::$controllerClass . ' from ' . self::$controllerFile);
    }

    /**
     * Load cached data
     * @throws \Difra\View\HttpError
     * @return bool
     */
    private static function loadCache(): bool
    {
        if (!$data = Cache::getInstance()->get(self::getCacheKey())) {
            return false;
        }
        switch ($data['result']) {
            case 'action':
                foreach ($data['vars'] as $key => $value) {
                    /** @noinspection PhpVariableVariableInspection */
                    self::${$key} = $value;
                }
                include_once(self::$controllerFile);
                break;
            case '404':
                throw new HttpError(404);
        }
        return true;
    }

    /**
     * Cache data
     * @param string $result Result type: 'action' or '404'
     */
    private static function saveCache(string $result = 'action')
    {
        if ($result != '404') {
            // save main variables
            $match = [
                'vars' => [
                    'controllerClass' => self::$controllerClass,
                    'controllerFile' => self::$controllerFile,
                    'controllerUri' => self::$controllerUri,
                    'parameters' => self::$parameters
                ],
                'result' => $result
            ];
            // save action types variables
            foreach (self::$methodTypes as $methodType) {
                $methodVar = "method$methodType[0]$methodType[1]";
                /** @noinspection PhpVariableVariableInspection */
                $match['vars'][$methodVar] = self::${$methodVar};
            }
        } else {
            $match = [
                'result' => '404'
            ];
        }

        Cache::getInstance()->put(self::getCacheKey(), $match);
    }

    /**
     * Resource (JS, CSS, etc.) request processor
     * @param string[] $parts
     * @return void
     * @throws \Difra\View\HttpError|\Difra\Exception
     */
    private static function getResource(array $parts): void
    {
        if (sizeof($parts) != 2) {
            return;
        }
        $resourcer = Resourcer::getInstance($parts[0], true);
        if ($resourcer and $resourcer->isPrintable()) {
            try {
                if (!$resourcer->view($parts[1])) {
                    throw new HttpError(404);
                }
                View::$rendered = true;
                die();
            } catch (Exception) {
                throw new HttpError(404);
            }
        }
    }

    /**
     * Get controller object
     * @throws \Difra\Exception|\Difra\View\HttpError
     */
    public static function getController()
    {
        if (!self::$controllerClass) {
            self::find();
        }
        if (!self::$controllerObj) {
            self::$controllerObj = new self::$controllerClass(self::$parameters);
            if (!self::$controllerObj instanceof Controller) {
                throw new Exception('Controller should extend \Difra\Controller: ' . self::$controllerClass);
            }
        }
        return self::$controllerObj;
    }

    /**
     * Get list of controllers directories
     * @return string[]
     */
    public static function getControllerPaths(): array
    {
        static $controllerDirs = null;
        if (!is_null($controllerDirs)) {
            return $controllerDirs;
        }
        $controllerDirs = Roots::get(Roots::FIRST_APP);
        foreach ($controllerDirs as $key => $value) {
            $controllerDirs[$key] = $value . '/controllers';
        }
        return $controllerDirs;
    }

    /**
     * Get cache record key
     * @return string
     */
    private static function getCacheKey(): string
    {
        return 'action:uri:' . Envi::getUri();
    }

    /**
     * Find the deepest controller directory path for current REQUEST
     * @param string[] $parts
     * @return string[]
     */
    private static function findControllerDirs(array &$parts): array
    {
        $path = '';
        $depth = 0;
        $controllerDirs = $dirs = self::getControllerPaths();
        foreach ($parts as $part) {
            $path .= "/$part";
            $newDirs = [];
            foreach ($controllerDirs as $nextDir) {
                if (is_dir($nextDir . $path)) {
                    $newDirs[] = $nextDir . $path;
                }
            }
            if (empty($newDirs)) {
                break;
            }
            $depth++;
            $dirs = $newDirs;
        }
        self::$controllerClass = array_slice($parts, 0, $depth);
        $parts = array_slice($parts, $depth);
        return $dirs;
    }

    /**
     * Find matching controller for current URI
     * @param $parts
     * @return null|string
     */
    private static function findController(&$parts): ?string
    {
        $dirs = self::findControllerDirs($parts);
        $cName = $controllerFile = null;
        if (!empty($parts)) {
            foreach ($dirs as $tmpDir) {
                if (is_file("$tmpDir/$parts[0].php")) {
                    $cName = $parts[0];
                    $controllerFile = "$tmpDir/$cName.php";
                    break;
                }
            }
        }
        if (!$cName) {
            foreach ($dirs as $tmpDir) {
                if (is_file("$tmpDir/index.php")) {
                    $cName = 'index';
                    $controllerFile = "$tmpDir/index.php";
                    break;
                }
            }
        }
        if (!$cName) {
            return null;
        }
        if ($cName != 'index') {
            array_shift($parts);
        }
        self::$controllerClass[] = $cName;
        foreach (self::$controllerClass as $key => $value) {
            self::$controllerClass[$key] = ucfirst($value);
        }
        self::$controllerClass = '\\Controller\\' . implode('\\', self::$controllerClass);
        return $controllerFile;
    }

    /**
     * Find matching action for current URI
     * @param string[] $parts
     * @return void
     */
    private static function findAction(array &$parts): void
    {
        $foundMethod = false;
        $methodNames = !empty($parts) ? [$parts[0], 'index'] : ['index'];
        foreach ($methodNames as $methodTmp) {
            foreach (self::$methodTypes as $methodType) {
                if (method_exists(
                    self::$controllerClass,
                    $method = $methodTmp . $methodType[0] . 'Action' . $methodType[1]
                )) {
                    $foundMethod = $methodTmp;
                    $methodVar = "method$methodType[0]$methodType[1]";
                    /** @noinspection PhpVariableVariableInspection */
                    self::${$methodVar} = $method;
                }
            }
            if ($foundMethod and $foundMethod != 'index') {
                self::$actionUri = self::$controllerUri . '/' . array_shift($parts);
                break;
            }
        }
    }

    /**
     * Get matched controller name
     * @return array|string|null
     */
    public static function getControllerClass(): array|string|null
    {
        return self::$controllerClass;
    }

    /**
     * Manually set controller and action
     * @param string $controllerClass
     * @param string $actionMethod
     * @param array $parameters
     */
    public static function setCustomAction(string $controllerClass, string $actionMethod, array $parameters = [])
    {
        self::$controllerClass = $controllerClass;
        self::$method = $actionMethod;
        self::$parameters = $parameters;
    }

    /**
     * Get URI matching current controller
     * Useful for relative paths.
     * @return ?string
     */
    public static function getControllerUri(): ?string
    {
        return self::$controllerUri;
    }

    /**
     * Get URI matching current action
     * @return string|null
     */
    public static function getActionUri(): ?string
    {
        return self::$actionUri;
    }
}
