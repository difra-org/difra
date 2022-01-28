<?php

namespace Difra\Param;

use Difra\Envi\Setup;
use Difra\Exception;
use Difra\Security\Filter;

/**
 * Class Common
 * @package Difra\Param
 */
abstract class Common
{
    // consts to be redefined
    const type = null;
    const source = null;
    const named = null;
    const auto = false;
    // constants: source
    const SOURCE_AJAX = 'ajax';
    const SOURCE_QUERY = 'query';
    // constants: named
    const NAMED_TRUE = true;
    const NAMED_FALSE = false;
    // constants: date
    const TYPE_DATE = 'date';
    // fields
    /** @var mixed */
    protected mixed $value = null;

    /**
     * @param array|string $value
     * @throws Exception
     */
    public function __construct(array|string $value = '')
    {
        switch (static::type) {
            case 'data':
            case 'file':
                $this->value = $value;
                return;
            case 'files':
                $files = [];
                if (!empty($value)) {
                    foreach ($value as $file) {
                        if ($file['error'] == UPLOAD_ERR_OK) {
                            $files[] = new AjaxFile($file);
                        }
                    }
                }
                $this->value = $files;
                return;
        }
        $this->value = self::canonicalize($value);
        $this->value = match (static::type) {
            'string' => Filter\Strings::sanitize($value),
            'int' => Filter\Ints::sanitize($value),
            'float' => Filter\Floats::sanitize($value),
            'url' => Filter\URL::sanitize($value),
            'email' => Filter\Email::sanitize($value),
            'ip' => Filter\IP::sanitize($value),
            'datetime' => Filter\Datetime::sanitize($value),
            self::TYPE_DATE => Filter\Date::sanitize($value),
            'phone' => Filter\Phone::sanitize($value),
            'bankcard' => Filter\Bankcard::sanitize($value),
            default => throw new Exception('No wrapper for type ' . (static::type) . ' in Param\Common constructor.'),
        };
    }

    /**
     * @param string $str
     * @return string|null
     */
    private static function canonicalize(string $str): ?string
    {
        try {
            if (!mb_check_encoding($str, Setup::getEncoding())) {
                return null;
            }
            return $str;
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Verify parameter value
     * @param $value
     * @return bool
     * @throws Exception
     */
    public static function verify($value): bool
    {
        switch (static::type) {
            case 'file':
                if (!isset($value['error']) or $value['error'] !== UPLOAD_ERR_OK) {
                    return false;
                }
                return true;
            case 'files':
                if (!is_array($value) or empty($value)) {
                    return false;
                }
                foreach ($value as $fileData) {
                    if ($fileData['error'] === UPLOAD_ERR_OK) {
                        return true;
                    }
                }
                return false;
            case 'data':
                return true;
        }
        if (is_array($value) or is_object($value)) {
            return false;
        }
        $value = self::canonicalize($value);
        return match (static::type) {
            'string' => Filter\Strings::validate($value),
            'int' => Filter\Ints::validate($value),
            'float' => Filter\Floats::validate($value),
            'url' => Filter\URL::validate($value),
            'email' => Filter\Email::validate($value),
            'ip' => Filter\IP::validate($value),
            'datetime' => Filter\Datetime::validate($value),
            self::TYPE_DATE => Filter\Date::validate($value),
            'phone' => Filter\Phone::validate($value),
            'bankcard' => Filter\Bankcard::validate($value),
            default => throw new Exception('Can\'t check param of type: ' . static::type),
        };
    }

    /**
     * Get parameter source
     * @return string
     */
    public static function getSource(): string
    {
        return static::source;
    }

    /**
     * Is named field
     * @return bool
     */
    public static function isNamed(): bool
    {
        return static::named;
    }

    /**
     * Has auto value
     * @return bool
     */
    public static function isAuto(): bool
    {
        return defined('static::auto') && static::auto;
    }

    /**
     * Get string value
     * @return string
     */
    public function __toString()
    {
        $value = $this->val();
        if (is_array($value)) {
            return '';
        }
        return (string)$this->val();
    }

    /**
     * Get field value
     * @return mixed
     */
    public function val(): mixed
    {
        switch (static::type) {
            case 'file':
                if ($this->value['error'] === UPLOAD_ERR_OK) {
                    return file_get_contents($this->value['tmp_name']);
                }
                return null;
            case 'files':
                $res = [];
                foreach ($this->value as $file) {
                    /** @var AjaxFile $file */
                    $res[] = $file->val();
                }
                return $res;
            default:
                return $this->value;
        }
    }

    /**
     * Get raw $this->value value
     * @return mixed
     */
    public function raw(): mixed
    {
        return $this->value;
    }
}
