<?php

namespace UkrainianDeclension\Utils;

use UkrainianDeclension\Enums\NounSubgroup;

class WordHelper
{
    // Static arrays for better performance
    private const MASCULINE_EXCEPTIONS = ['тато', 'батько', 'дідо', 'петро', 'микола'];
    private const FEMININE_EXCEPTIONS = ['мати', 'ніч', 'осінь', 'сіль', 'любов', 'тінь'];
    private const NEUTER_EXCEPTIONS = ['життя', 'щастя', 'ягня', 'кошеня', 'теля', 'ім\'я'];
    private const FEMININE_ENDINGS = ['а', 'я'];
    private const NEUTER_ENDINGS = ['о', 'е'];
    private const SOFT_CONSONANT_ENDINGS = ['ь', 'й'];

    public static function getStem(string $word): string
    {
        if ($word === 'стіл') {
            return 'стол';
        }
        if ($word === 'кінь') {
            return 'кон';
        }

        $last_char = mb_substr($word, -1);

        if (in_array($last_char, self::SOFT_CONSONANT_ENDINGS)) {
            return mb_substr($word, 0, -1);
        }

        return $word;
    }

    public static function endsWith(string $haystack, $needles): bool
    {
        if (is_array($needles)) {
            foreach ($needles as $needle) {
                if (self::endsWith($haystack, $needle)) {
                    return true;
                }
            }
            return false;
        }

        $length = mb_strlen($needles);
        if ($length === 0) {
            return true;
        }

        return mb_substr($haystack, -$length) === $needles;
    }

    public static function palatalize(string $stem): string
    {
        $last_char = mb_substr($stem, -1);
        $base = mb_substr($stem, 0, -1);
        switch ($last_char) {
            case 'г':
                return $base . 'з';
            case 'к':
                return $base . 'ц';
            case 'х':
                return $base . 'с';
            default:
                return $stem;
        }
    }

    public static function guessGender(string $word): ?\UkrainianDeclension\Enums\Gender
    {
        $word_lower = mb_strtolower($word);

        if (in_array($word_lower, self::MASCULINE_EXCEPTIONS, true)) {
            return \UkrainianDeclension\Enums\Gender::MASCULINE;
        }

        if (in_array($word_lower, self::FEMININE_EXCEPTIONS, true)) {
            return \UkrainianDeclension\Enums\Gender::FEMININE;
        }

        if (in_array($word_lower, self::NEUTER_EXCEPTIONS, true)) {
            return \UkrainianDeclension\Enums\Gender::NEUTER;
        }

        // General rules based on word endings.
        if (self::endsWith($word_lower, self::FEMININE_ENDINGS)) {
            return \UkrainianDeclension\Enums\Gender::FEMININE;
        }

        if (self::endsWith($word_lower, self::NEUTER_ENDINGS)) {
            return \UkrainianDeclension\Enums\Gender::NEUTER;
        }

        return \UkrainianDeclension\Enums\Gender::MASCULINE;
    }

    /**
     * Copy letter case pattern from source word to target word
     * Similar to shevchenko-js copyLetterCase function
     */
    public static function copyLetterCase(string $source, string $target): string
    {
        $sourceLength = mb_strlen($source);
        $targetLength = mb_strlen($target);
        
        if ($sourceLength === 0 || $targetLength === 0) {
            return $target;
        }

        // Cache case computations to avoid repeated calls
        $sourceUpper = mb_strtoupper($source);
        $sourceLower = mb_strtolower($source);
        
        // If source is all uppercase, return target in uppercase
        if ($sourceUpper === $source && $sourceLower !== $source) {
            return mb_strtoupper($target);
        }

        // If source is all lowercase, return target in lowercase
        if ($sourceLower === $source) {
            return mb_strtolower($target);
        }

        // If source starts with uppercase (title case), make target title case
        $firstChar = mb_substr($source, 0, 1);
        $firstCharUpper = mb_strtoupper($firstChar);
        $firstCharLower = mb_strtolower($firstChar);
        
        if ($firstCharUpper === $firstChar && $firstCharLower !== $firstChar) {
            return mb_strtoupper(mb_substr($target, 0, 1)) . mb_strtolower(mb_substr($target, 1));
        }

        // Default: return target as-is
        return $target;
    }

    /**
     * Check if a word is all uppercase
     */
    public static function isWordUppercase(string $word): bool
    {
        $upper = mb_strtoupper($word);
        $lower = mb_strtolower($word);
        return $upper === $word && $lower !== $word;
    }

    /**
     * Check if a word is title case (first letter uppercase, rest lowercase)
     */
    public static function isTitleCase(string $word): bool
    {
        $length = mb_strlen($word);
        if ($length === 0) {
            return false;
        }
        
        $firstChar = mb_substr($word, 0, 1);
        $firstCharUpper = mb_strtoupper($firstChar);
        $firstCharLower = mb_strtolower($firstChar);
        
        if ($firstCharUpper !== $firstChar || $firstCharLower === $firstChar) {
            return false;
        }
        
        if ($length === 1) {
            return true;
        }
        
        $restChars = mb_substr($word, 1);
        return mb_strtolower($restChars) === $restChars;
    }
} 