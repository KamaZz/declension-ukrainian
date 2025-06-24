<?php

namespace UkrainianDeclension\Services;

use UkrainianDeclension\Contracts\DeclensionGroupIdentifierContract;
use UkrainianDeclension\Enums\Declension;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Exceptions\UnsupportedWordException;
use UkrainianDeclension\Utils\WordHelper;

class DeclensionGroupIdentifier implements DeclensionGroupIdentifierContract
{
    // Static arrays for better performance
    private const INDECLINABLE_SUFFIXES = ['енко', 'ко', 'ло'];
    private const INDECLINABLE_FEMININE_SURNAMES = ['Голуб', 'Боровик', 'Присяжнюк'];
    private const FIRST_DECLENSION_ENDINGS = ['а', 'я'];
    private const FOURTH_DECLENSION_ENDINGS = ['а', 'я'];

    /**
     * @inheritDoc
     */
    public function identify(string $word, Gender $gender): Declension
    {
        $lowerWord = mb_strtolower($word);
        $last_char = mb_substr($lowerWord, -1);

        if ($gender === Gender::FEMININE) {
            foreach (self::INDECLINABLE_SUFFIXES as $suffix) {
                if (WordHelper::endsWith($lowerWord, $suffix)) {
                    return Declension::INDECLINABLE;
                }
            }
        }
        
        if ($gender === Gender::FEMININE && in_array($word, self::INDECLINABLE_FEMININE_SURNAMES, true)) {
            return Declension::INDECLINABLE;
        }

        // Fourth declension: neuter nouns ending in -а, -я
        if ($gender === Gender::NEUTER && in_array($last_char, self::FOURTH_DECLENSION_ENDINGS)) {
            return Declension::FOURTH;
        }

        // First declension: feminine, masculine, common nouns ending in -а, -я
        if (in_array($last_char, self::FIRST_DECLENSION_ENDINGS)) {
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

        throw new UnsupportedWordException("Unable to identify declension group for word: {$word}");
    }
} 