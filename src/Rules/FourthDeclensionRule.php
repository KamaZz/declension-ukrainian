<?php

namespace UkrainianDeclension\Rules;

use UkrainianDeclension\Contracts\DeclensionRuleContract;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Number;

class FourthDeclensionRule implements DeclensionRuleContract
{
    public function decline(string $word, GrammaticalCase $case, Number $number): string
    {
        if ($number === Number::PLURAL) {
            return $this->declinePlural($word, $case);
        }

        return $this->declineSingular($word, $case);
    }
    
    public function declineSingular(string $word, GrammaticalCase $case): string
    {
        // For Nominative, Accusative, and Vocative, the form is unchanged in singular.
        if (in_array($case, [GrammaticalCase::NOMINATIVE, GrammaticalCase::ACCUSATIVE, GrammaticalCase::VOCATIVE])) {
            return $word;
        }

        $stem = mb_substr($word, 0, -1);
        $suffix = $this->getSuffix($word);

        // Handle abstract nouns ending in -я (like життя, знання)
        if ($this->isAbstractNoun($word)) {
            switch ($case) {
                case GrammaticalCase::GENITIVE:
                    // Abstract nouns in -я don't change in genitive (життя → життя)
                    return $word;
                case GrammaticalCase::DATIVE:
                case GrammaticalCase::LOCATIVE:
                    return $stem . 'ю';
                case GrammaticalCase::INSTRUMENTAL:
                    return $stem . 'ям';
            }
        }

        // The word 'імʼя' and its group have a specific declension pattern.
        if ($suffix === 'ен') {
            $clean_stem = str_replace('\'', '', $stem);
            switch ($case) {
                case GrammaticalCase::INSTRUMENTAL:
                    return $clean_stem . 'енем';
                default: // Genitive, Dative, Locative
                    return $clean_stem . 'ені';
            }
        }

        // For baby animals (ягня, теля, etc.)
        switch ($case) {
            case GrammaticalCase::GENITIVE:
                return $stem . $suffix . 'и'; // кошеня → кошеняти
            case GrammaticalCase::DATIVE:
            case GrammaticalCase::LOCATIVE:
                return $stem . $suffix . 'і'; // ягня → ягняті
            case GrammaticalCase::INSTRUMENTAL:
                return $stem . $suffix . 'ям';
        }

        return $word;
    }

    public function declinePlural(string $word, GrammaticalCase $case): string
    {
        $suffix = $this->getSuffix($word);
        $stem = mb_substr($word, 0, -1);
        $plural_stem = $stem . $suffix;
        
        if ($suffix === 'ен') {
             $clean_stem = str_replace('\'', '', $stem);
             $plural_stem = $clean_stem . $suffix;
        }

        switch ($case) {
            case GrammaticalCase::GENITIVE:
                return $plural_stem;
            case GrammaticalCase::DATIVE:
                return $plural_stem . 'ам';
            case GrammaticalCase::ACCUSATIVE:
                // Simplified
                return $this->declinePlural($word, GrammaticalCase::NOMINATIVE);
            case GrammaticalCase::INSTRUMENTAL:
                return $plural_stem . 'ами';
            case GrammaticalCase::LOCATIVE:
                return $plural_stem . 'ах';
            case GrammaticalCase::VOCATIVE:
                return $this->declinePlural($word, GrammaticalCase::NOMINATIVE);
            default: // NOMINATIVE
                return $plural_stem . 'а';
        }
    }
    
    private function getSuffix(string $word): string
    {
        if (in_array($word, ['ім\'я', 'плем\'я', 'сім\'я', 'тім\'я'])) {
            return 'ен';
        }
        return 'ят'; // a simplification, can also be 'ат'
    }

    private function isAbstractNoun(string $word): bool
    {
        // Abstract nouns ending in -я that don't change in genitive
        $abstractNouns = [
            'життя', 'знання', 'читання', 'писання', 'розуміння', 
            'навчання', 'кохання', 'страждання', 'бажання', 'мислення'
        ];
        
        return in_array($word, $abstractNouns);
    }
} 