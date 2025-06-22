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

        if ($last_two_chars === 'сть' || in_array($last_char, ['ж', 'ч', 'ш'])) {
            return $word . 'ю';
        }

        // Double the consonant
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
