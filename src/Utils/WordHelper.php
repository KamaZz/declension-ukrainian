<?php

namespace UkrainianDeclension\Utils;

use UkrainianDeclension\Enums\NounSubgroup;

class WordHelper
{
    public static function getStem(string $word): string
    {
        if ($word === 'стіл') {
            return 'стол';
        }
        if ($word === 'кінь') {
            return 'кон';
        }

        $last_char = mb_substr($word, -1);

        if (in_array($last_char, ['ь', 'й'])) {
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

        $masculine_exceptions = ['тато', 'батько', 'дідо', 'петро', 'микола'];
        if (in_array($word_lower, $masculine_exceptions, true)) {
            return \UkrainianDeclension\Enums\Gender::MASCULINE;
        }

        $feminine_exceptions = ['мати', 'ніч', 'осінь', 'сіль', 'любов', 'тінь'];
        if (in_array($word_lower, $feminine_exceptions, true)) {
            return \UkrainianDeclension\Enums\Gender::FEMININE;
        }

        $neuter_exceptions = ['життя', 'щастя', 'ягня', 'кошеня', 'ім\'я'];
        if (in_array($word_lower, $neuter_exceptions, true)) {
            return \UkrainianDeclension\Enums\Gender::NEUTER;
        }

        // General rules based on word endings.
        if (self::endsWith($word_lower, ['а', 'я'])) {
            return \UkrainianDeclension\Enums\Gender::FEMININE;
        }

        if (self::endsWith($word_lower, ['о', 'е'])) {
            return \UkrainianDeclension\Enums\Gender::NEUTER;
        }

        return \UkrainianDeclension\Enums\Gender::MASCULINE;
    }
} 