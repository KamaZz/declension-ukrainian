<?php

namespace UkrainianDeclension\Services;

use UkrainianDeclension\Contracts\DeclensionGroupIdentifierContract;
use UkrainianDeclension\Enums\Declension;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Exceptions\UnsupportedWordException;
use UkrainianDeclension\Utils\WordHelper;

class DeclensionGroupIdentifier implements DeclensionGroupIdentifierContract
{
    /**
     * @inheritDoc
     */
    public function identify(string $word, Gender $gender): Declension
    {
        $last_char = mb_strtolower(mb_substr($word, -1));

        $indeclinable_suffixes = ['енко', 'ко', 'ло'];
        if ($gender === Gender::FEMININE) {
            foreach ($indeclinable_suffixes as $suffix) {
                if (WordHelper::endsWith(mb_strtolower($word), $suffix)) {
                    return Declension::INDECLINABLE;
                }
            }
        }
        
        $indeclinable_feminine_surnames = ['Голуб', 'Боровик', 'Присяжнюк'];
        if ($gender === Gender::FEMININE && in_array($word, $indeclinable_feminine_surnames, true)) {
            return Declension::INDECLINABLE;
        }

        // Fourth declension: neuter nouns ending in -а, -я
        if ($gender === Gender::NEUTER && in_array($last_char, ['а', 'я'])) {
            return Declension::FOURTH;
        }

        // First declension: feminine, masculine, common nouns ending in -а, -я
        if (in_array($last_char, ['а', 'я'])) {
            return Declension::FIRST;
        }

        // Third declension: feminine nouns ending in a consonant (including soft sign)
        if ($gender === Gender::FEMININE) {
            return Declension::THIRD;
        }
        
        // Second declension: masculine nouns with zero ending or -о, and neuter nouns in -о, -е.
        if ($gender === Gender::MASCULINE || $gender === Gender::NEUTER) {
            return Declension::SECOND;
        }

        throw new UnsupportedWordException("Could not determine declension group for word '{$word}'.");
    }
} 