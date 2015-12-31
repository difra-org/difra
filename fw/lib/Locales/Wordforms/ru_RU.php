<?php

namespace Difra\Locales\Wordforms;

use Difra\Locales\Wordforms;

/**
 * Class ru_RU
 * Russian language word forms implementation.
 * WARNING: IT'S DRAFT!
 * @package Difra\Locales\Wordforms
 */
class ru_RU extends Common
{
    private static $genders = [
        Wordforms::GENDER_MALE,     // мужской род
        Wordforms::GENDER_FEMALE,   // женский род
        Wordforms::GENDER_NEUTER    // средний род
    ];

    private static $cases = [
        Wordforms::CASE_NOMINATIVE, // именительный падеж
        Wordforms::CASE_GENITIVE,   // родительный падеж
        Wordforms::CASE_DATIVE,     // дательный падеж
        Wordforms::CASE_ACCUSATIVE, // винительный падеж
        Wordforms::CASE_ABLATIVE,   // творительный падеж
        Wordforms::CASE_LOCATIVE    // предложный падеж
    ];

    private static $numbers = [
        Wordforms::NUMBER_SINGLE,   // единственное число
        Wordforms::NUMBER_MULTIPLE  // множественное число
    ];

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
        switch ($form & (Wordforms::MASK_GENDER|Wordforms::MASK_CASE|Wordforms::MASK_NUMBER)) {
            case Wordforms::GENDER_MALE | Wordforms::CASE_NOMINATIVE | Wordforms::NUMBER_SINGLE:
                // мужской род, именительный падеж, единственное число
                return $word;
            case Wordforms::GENDER_MALE | Wordforms::CASE_NOMINATIVE | Wordforms::NUMBER_MULTIPLE;
                // мужской род, именительный падеж, множественное число
                return $word . 'ы';
            case Wordforms::GENDER_MALE | Wordforms::CASE_GENITIVE | Wordforms::NUMBER_SINGLE:
                // мужской род, родительный падеж, единственное число
                return $word . 'а';
            case Wordforms::GENDER_MALE | Wordforms::CASE_GENITIVE | Wordforms::NUMBER_MULTIPLE;
                // мужской род, родительный падеж, множественное число
                return $word . 'ов';
            case Wordforms::GENDER_MALE | Wordforms::CASE_DATIVE | Wordforms::NUMBER_SINGLE;
                // мужской род, дательный падеж, единственное число
                return $word . 'у';
            case Wordforms::GENDER_MALE | Wordforms::CASE_DATIVE | Wordforms::NUMBER_MULTIPLE:
                // мужской род, дательный падеж, множественное число
                return $word . 'ам';
            case Wordforms::GENDER_MALE | Wordforms::CASE_ACCUSATIVE | Wordforms::NUMBER_SINGLE:
                // мужской род, винительный падеж, единственное число
                return $word;
            case Wordforms::GENDER_MALE | Wordforms::CASE_ACCUSATIVE | Wordforms::NUMBER_MULTIPLE:
                // мужской род, винительный падеж, множественное число
                return $word . 'ы';
            case Wordforms::GENDER_MALE | Wordforms::CASE_ABLATIVE | Wordforms::NUMBER_SINGLE:
                // мужской род, творительный падеж, единственное число
                return $word . 'ом';
            case Wordforms::GENDER_MALE | Wordforms::CASE_ABLATIVE | Wordforms::NUMBER_MULTIPLE:
                // мужской род, творительный падеж, множественное число
                return $word . 'ами';
            case Wordforms::GENDER_MALE | Wordforms::CASE_LOCATIVE | Wordforms::NUMBER_SINGLE:
                // мужской род, предложный падеж, единственное число
                return $word . 'e';
            case Wordforms::GENDER_MALE | Wordforms::CASE_LOCATIVE | Wordforms::NUMBER_MULTIPLE:
                // мужской род, предложный падеж, множественное число
                return $word . 'ах';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_NOMINATIVE | Wordforms::NUMBER_SINGLE:
                // женский род, именительный падеж, единственное число
                return $word;
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_NOMINATIVE | Wordforms::NUMBER_MULTIPLE:
                // женский род, именительный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'и';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_GENITIVE | Wordforms::NUMBER_SINGLE:
                // женский род, родительный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'и';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_GENITIVE | Wordforms::NUMBER_MULTIPLE:
                // женский род, родительный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1);
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_DATIVE | Wordforms::NUMBER_SINGLE:
                // женский род, дательный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'е';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_DATIVE | Wordforms::NUMBER_MULTIPLE:
                // женский род, дательный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'ам';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_ACCUSATIVE | Wordforms::NUMBER_SINGLE:
                // женский род, винительный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'у';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_ACCUSATIVE | Wordforms::NUMBER_MULTIPLE:
                // женский род, винительный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1);
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_ABLATIVE | Wordforms::NUMBER_SINGLE:
                // женский род, творительный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'ой';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_ABLATIVE | Wordforms::NUMBER_MULTIPLE:
                // женский род, творительный падеж, множественное число
                return $word . 'ми';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_LOCATIVE | Wordforms::NUMBER_SINGLE:
                // женский род, предложный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'e';
            case Wordforms::GENDER_FEMALE | Wordforms::CASE_LOCATIVE | Wordforms::NUMBER_MULTIPLE:
                // женский род, предложный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'ах';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_NOMINATIVE | Wordforms::NUMBER_SINGLE:
                // средний род, именительный падеж, единственное число
                return $word;
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_NOMINATIVE | Wordforms::NUMBER_MULTIPLE:
                // средний род, именительный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'и';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_GENITIVE | Wordforms::NUMBER_SINGLE:
                // средний род, родительный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'а';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_GENITIVE | Wordforms::NUMBER_MULTIPLE:
                // средний род, родительный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1);
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_DATIVE | Wordforms::NUMBER_SINGLE:
                // средний род, дательный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'у';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_DATIVE | Wordforms::NUMBER_MULTIPLE:
                // средний род, дательный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'ам';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_ACCUSATIVE | Wordforms::NUMBER_SINGLE:
                // средний род, винительный падеж, единственное число
                return $word;
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_ACCUSATIVE | Wordforms::NUMBER_MULTIPLE:
                // средний род, винительный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'и';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_ABLATIVE | Wordforms::NUMBER_SINGLE:
                // средний род, творительный падеж, единственное число
                return $word . 'м';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_ABLATIVE | Wordforms::NUMBER_MULTIPLE:
                // средний род, творительный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'ами';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_LOCATIVE | Wordforms::NUMBER_SINGLE:
                // средний род, предложный падеж, единственное число
                return mb_substr($word, 0, strlen($word) - 1) . 'e';
            case Wordforms::GENDER_NEUTER | Wordforms::CASE_LOCATIVE | Wordforms::NUMBER_MULTIPLE:
                // средний род, предложный падеж, множественное число
                return mb_substr($word, 0, strlen($word) - 1) . 'ах';
            default:
                // not implemented
                return $word;
        }
    }

    /**
     * Get quantity-based form
     * @param string $word
     * @param int $form
     * @param int $quantity
     * @return string
     */
    public function getQuantityForm($word, $form, $quantity)
    {
        if (!in_array(($quantity % 100), array(11, 12, 13, 14, 15, 16, 17, 18, 19)) and ($quantity % 10 == 1)) {
            return self::getForm($word, $form & ~Wordforms::MASK_NUMBER | Wordforms::NUMBER_SINGLE);
        } else {
            return self::getForm($word, $form & ~Wordforms::MASK_NUMBER | Wordforms::NUMBER_MULTIPLE);
        }
    }
}
