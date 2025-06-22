<?php

namespace UkrainianDeclension\Rules;

use UkrainianDeclension\Contracts\DeclensionRuleContract;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Number;

class ThirdDeclensionRule implements DeclensionRuleContract
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
        if ($word === 'мати') {
            return $this->declineMother($case);
        }

        if ($word === 'ніч' && in_array($case, [GrammaticalCase::GENITIVE, GrammaticalCase::DATIVE, GrammaticalCase::LOCATIVE])) {
            return 'ночі';
        }

        switch ($case) {
            case GrammaticalCase::GENITIVE:
            case GrammaticalCase::DATIVE:
            case GrammaticalCase::LOCATIVE:
                // Handle soft consonants properly
                $last_two = mb_substr($word, -2);
                $last_char = mb_substr($word, -1);
                
                // Special handling for words ending in 'інь'
                if (mb_substr($word, -3) === 'інь') {
                    // осінь → осені (remove інь, add ені)
                    return mb_substr($word, 0, -3) . 'ені';
                }
                
                // Other words ending in soft sign
                if ($last_char === 'ь') {
                    // Default: remove soft sign and add -і
                    return mb_substr($word, 0, -1) . 'і';
                }
                
                // Words not ending in soft sign
                return $word . 'і';

            case GrammaticalCase::INSTRUMENTAL:
                return $this->getInstrumental($word);

            case GrammaticalCase::VOCATIVE:
                return $word . 'е';

            case GrammaticalCase::NOMINATIVE:
            case GrammaticalCase::ACCUSATIVE:
            default:
                return $word;
        }
    }

    public function declinePlural(string $word, GrammaticalCase $case): string
    {
         if ($word === 'мати') {
            switch ($case) {
                case GrammaticalCase::GENITIVE: return 'матерів';
                case GrammaticalCase::DATIVE: return 'матерям';
                case GrammaticalCase::ACCUSATIVE: return 'матерів';
                case GrammaticalCase::INSTRUMENTAL: return 'матерями';
                case GrammaticalCase::LOCATIVE: return 'матерях';
                default: return 'матері'; // NOMINATIVE, VOCATIVE
            }
        }

        if ($word === 'ніч' && in_array($case, [GrammaticalCase::NOMINATIVE, GrammaticalCase::VOCATIVE, GrammaticalCase::ACCUSATIVE])) {
            return 'ночі';
        }

        switch ($case) {
            case GrammaticalCase::GENITIVE:
                return $word . 'ей';
            case GrammaticalCase::DATIVE:
                return $word . 'ям';
            case GrammaticalCase::ACCUSATIVE:
                // simplified
                return $this->declinePlural($word, GrammaticalCase::NOMINATIVE);
            case GrammaticalCase::INSTRUMENTAL:
                 return $word . 'ями';
            case GrammaticalCase::LOCATIVE:
                 return $word . 'ях';
            case GrammaticalCase::VOCATIVE:
                 return $this->declinePlural($word, GrammaticalCase::NOMINATIVE);
            default: // NOMINATIVE
                return $word . 'і';
        }
    }

    protected function getInstrumental(string $word): string
    {
        $last_char = mb_substr($word, -1);
        $last_two_chars = mb_substr($word, -2);

        // Special cases that don't double consonants
        if ($last_two_chars === 'сть') {
            return $word . 'ю';
        }
        
        // Handle soft sign first
        if ($last_char === 'ь') {
            $consonant_before_soft = mb_substr($word, -2, 1);
            // Remove soft sign and double the consonant if needed
            if (in_array($consonant_before_soft, ['л', 'н', 'т', 'д', 'р', 'с', 'з', 'ц'])) {
                return mb_substr($word, 0, -1) . $consonant_before_soft . 'ю'; // сіль → сілль → сіллю
            }
            return mb_substr($word, 0, -1) . 'ю';
        }
        
        // Consonants that need doubling
        if (in_array($last_char, ['т', 'д', 'н', 'л', 'р', 'с', 'з', 'ц'])) {
            return $word . $last_char . 'ю';
        }
        
        // Special case for 'ч' - it doubles (ніч → ніччю)
        if ($last_char === 'ч') {
            return $word . 'чю';
        }
        
        // Consonants that don't double but take -ю
        if (in_array($last_char, ['ж', 'ш', 'щ'])) {
            return $word . 'ю';
        }
        


        // Default: double the consonant
        return $word . $last_char . 'ю';
    }

    protected function declineMother(GrammaticalCase $case): string
    {
        switch ($case) {
            case GrammaticalCase::GENITIVE:
            case GrammaticalCase::DATIVE:
            case GrammaticalCase::LOCATIVE:
                return 'матері';
            case GrammaticalCase::INSTRUMENTAL:
                return 'матір\'ю';
            case GrammaticalCase::VOCATIVE:
                return 'мати';
            case GrammaticalCase::NOMINATIVE:
            case GrammaticalCase::ACCUSATIVE:
            default:
                return 'мати';
        }
    }
}
