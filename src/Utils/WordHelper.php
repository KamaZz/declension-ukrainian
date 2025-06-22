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
} 