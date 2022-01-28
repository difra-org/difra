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
    protected $value = null;

    /**
     * @param string|array $value
     * @throws Exception
     */
    public function __construct($value = '')
    {
        switch (static::type) {
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
            case 'data':
                $this->value = $value;
                return;
        }
        $this->value = self::canonicalize($value);
        switch (static::type) {
            case 'string':
                $this->value = Filter\Strings::sanitize($value);
                break;
            case 'int':
                $this->value = Filter\Ints::sanitize($value);
                break;
            case 'float':
                $this->value = Filter\Floats::sanitize($value);
                break;
            case 'url':
                $this->value = Filter\URL::sanitize($value);
                break;
            case 'email':
                $this->value = Filter\Email::sanitize($value);
                break;
            case 'ip':
                $this->value = Filter\IP::sanitize($value);
                break;
            case 'datetime':
                $this->value = Filter\Datetime::sanitize($value);
                break;
            case self::TYPE_DATE:
                $this->value = Filter\Date::sanitize($value);
                break;
            case 'phone':
                $this->value = Filter\Phone::sanitize($value);
                break;
            case 'bankcard':
                $this->value = Filter\Bankcard::sanitize($value);
                break;
            default:
                throw new Exception('No wrapper for type ' . (static::type) . ' in Param\Common constructor.');
        }
    }

    /**
     * @param $str
     * @return string|null
     */
    private static function canonicalize(string $str): ?string
    {
        try {
            if (!mb_check_encoding($str, Setup::getEncoding())) {
                return null;
            }
            return $str;
        } catch (\Exception $ex) {
            return null;
        }
    }

    /**
     * Verify parameter value
     * @param $value
     * @return bool
     * @throws Exception
     */
    public static function verify($value)
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
        switch (static::type) {
            case 'string':
                return Filter\Strings::validate($value);
            case 'int':
                return Filter\Ints::validate($value);
            case 'float':
                return Filter\Floats::validate($value);
            case 'url':
                return Filter\URL::validate($value);
            case 'email':
                return Filter\Email::validate($value);
            case 'ip':
                return Filter\IP::validate($value);
            case 'datetime':
                return Filter\Datetime::validate($value);
            case self::TYPE_DATE:
                return Filter\Date::validate($value);
            case 'phone':
                return Filter\Phone::validate($value);
            case 'bankcard':
                return Filter\Bankcard::validate($value);
            default:
                throw new Exception('Can\'t check param of type: ' . static::type);
        }
    }

    /**
     * Get parameter source
     * @return self::SOURCE_AJAX|self::SOURCE_QUERY
     */
    public static function getSource()
    {
        return static::source;
    }

    /**
     * Is named field
     * @return self::NAMED_TRUE|self::NAMED_FALSE
     */
    public static function isNamed()
    {
        return static::named;
    }

    /**
     * Has auto value
     * @return bool
     */
    public static function isAuto()
    {
        return defined('static::auto') ? static::auto : false;
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
    public function val()
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
                    /** @var $file AjaxFile */
                    $res[] = $file->val();
                }
                return $res;
            default:
                return $this->value;
        }
    }

    /**
     * Get raw $this->value value
     * @return mixed|string
     */
    public function raw()
    {
        return $this->value;
    }
}
