<?php

namespace Difra\Libs\Debug;

/**
 * Class errorConstants
 * @package Difra\Libs\Debug
 */
class ErrorConstants
{
    /** @var array PHP errors matching */
    private const ERRORS = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED'
    ];

    /**
     * Singleton
     * @static
     * @return ErrorConstants
     */
    public static function getInstance(): ErrorConstants
    {

        static $instance = null;
        return $instance ?? $instance = new self();
    }

    /**
     * Get error text by error code
     * @param $code
     * @return string|null
     */
    public function getError($code): ?string
    {

        return static::ERRORS[$code] ?? null;
    }

    /**
     * Get humanized error text by error code
     * @param $code
     * @return null|string
     */
    public function getVerbalError($code): ?string
    {

        $error = $this->getError($code);
        if (is_null($error)) {
            return null;
        }
        if (str_starts_with($error, 'E_')) {
            $error = substr($error, 2);
        }
        return ucwords(strtolower(str_replace('_', ' ', $error)));
    }
}
