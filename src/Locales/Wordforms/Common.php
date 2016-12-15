<?php

namespace Difra\Locales\Wordforms;

use Difra\Locales\Wordforms;

class Common
{
    /** @var array Languages list for language */
    private static /** @noinspection PhpUnusedPrivateFieldInspection */
        $genders = [
        Wordforms::GENDER_NEUTER
    ];

    /** @var array Cases list for language */
    private static /** @noinspection PhpUnusedPrivateFieldInspection */
        $cases = [
        Wordforms::CASE_NOMINATIVE
    ];

    /** @var array Numbers list for language */
    private static /** @noinspection PhpUnusedPrivateFieldInspection */
        $numbers = [
        Wordforms::NUMBER_SINGLE,
        Wordforms::NUMBER_MULTIPLE
    ];

    /**
     * Forbid direct object creation
     */
    private function __construct()
    {
    }

    /**
     * Forbid cloning
     */
    private function __clone()
    {
    }

    /**
     * Singleton
     * @return Common
     */
    public static function getInstance()
    {
        static $instance = null;
        return $instance ?: $instance = new static;
    }

    /**
     * Get word form
     * @param string $word
     * @param int $form
     * @return string
     */
    public function getForm(
        $word,
        /** @noinspection PhpUnusedParameterInspection */
        $form = 0
    ) {
        return $word;
    }

    /**
     * Get word form for certain quantity
     * @param string $word
     * @param int $form
     * @param int $quantity
     * @return string
     */
    public function getQuantityForm(
        $word,
        /** @noinspection PhpUnusedParameterInspection */
        $form,
        /** @noinspection PhpUnusedParameterInspection */
        $quantity
    ) {
        return $word;
    }
}
