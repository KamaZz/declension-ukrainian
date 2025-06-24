<?php

namespace UkrainianDeclension\Services;

use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;

class AdjectiveDeclensioner
{
    public function decline(string $adjective, GrammaticalCase $case, Gender $gender, Number $number, bool $isAnimate = true): string
    {
        if ($number === Number::PLURAL) {
            return $this->declinePlural($adjective, $case, $isAnimate);
        }

        return match ($gender) {
            Gender::MASCULINE => $this->declineMasculine($adjective, $case, $isAnimate),
            Gender::FEMININE => $this->declineFeminine($adjective, $case),
            Gender::NEUTER => $this->declineNeuter($adjective, $case),
        };
    }

    protected function declineMasculine(string $adjective, GrammaticalCase $case, bool $isAnimate): string
    {
        $stem = $this->getMasculineStem($adjective);
        
        // Check if this is a soft adjective ending in -ній
        $lowerAdjective = mb_strtolower($adjective);
        $isSoftAdjective = mb_substr($lowerAdjective, -3) === 'ній';

        $result = match ($case) {
            GrammaticalCase::NOMINATIVE => $adjective,
            GrammaticalCase::GENITIVE => $isSoftAdjective ? $stem . 'нього' : $stem . 'ого',
            GrammaticalCase::DATIVE => $isSoftAdjective ? $stem . 'ньому' : $stem . 'ому',
            GrammaticalCase::ACCUSATIVE => $isAnimate ? 
                ($isSoftAdjective ? $stem . 'нього' : $stem . 'ого') : $adjective,
            GrammaticalCase::INSTRUMENTAL => $isSoftAdjective ? $stem . 'нім' : $stem . 'им',
            GrammaticalCase::LOCATIVE => $isSoftAdjective ? $stem . 'ньому' : $stem . 'ому',
            GrammaticalCase::VOCATIVE => $adjective,
        };

        // Preserve case if the result is different from the original
        if ($result !== $adjective) {
            return \UkrainianDeclension\Utils\WordHelper::copyLetterCase($adjective, $result);
        }

        return $result;
    }

    protected function declineFeminine(string $adjective, GrammaticalCase $case): string
    {
        $stem = $this->getFeminineStem($adjective);

        $result = match ($case) {
            GrammaticalCase::NOMINATIVE => $adjective,
            GrammaticalCase::GENITIVE => $stem . 'ої',
            GrammaticalCase::DATIVE => $stem . 'ій',
            GrammaticalCase::ACCUSATIVE => $stem . 'у',
            GrammaticalCase::INSTRUMENTAL => $stem . 'ою',
            GrammaticalCase::LOCATIVE => $stem . 'ій',
            GrammaticalCase::VOCATIVE => $adjective,
        };

        // Preserve case if the result is different from the original
        if ($result !== $adjective) {
            return \UkrainianDeclension\Utils\WordHelper::copyLetterCase($adjective, $result);
        }

        return $result;
    }

    protected function declineNeuter(string $adjective, GrammaticalCase $case): string
    {
        $stem = $this->getNeuterStem($adjective);

        $result = match ($case) {
            GrammaticalCase::NOMINATIVE => $adjective,
            GrammaticalCase::GENITIVE => $stem . 'ого',
            GrammaticalCase::DATIVE => $stem . 'ому',
            GrammaticalCase::ACCUSATIVE => $adjective,
            GrammaticalCase::INSTRUMENTAL => $stem . 'им',
            GrammaticalCase::LOCATIVE => $stem . 'ому',
            GrammaticalCase::VOCATIVE => $adjective,
        };

        // Preserve case if the result is different from the original
        if ($result !== $adjective) {
            return \UkrainianDeclension\Utils\WordHelper::copyLetterCase($adjective, $result);
        }

        return $result;
    }

    protected function declinePlural(string $adjective, GrammaticalCase $case, bool $isAnimate): string
    {
        $stem = $this->getPluralStem($adjective);

        $result = match ($case) {
            GrammaticalCase::NOMINATIVE => $adjective,
            GrammaticalCase::GENITIVE => $stem . 'их',
            GrammaticalCase::DATIVE => $stem . 'им',
            GrammaticalCase::ACCUSATIVE => $isAnimate ? $stem . 'их' : $adjective,
            GrammaticalCase::INSTRUMENTAL => $stem . 'ими',
            GrammaticalCase::LOCATIVE => $stem . 'их',
            GrammaticalCase::VOCATIVE => $adjective,
        };

        // Preserve case if the result is different from the original
        if ($result !== $adjective) {
            return \UkrainianDeclension\Utils\WordHelper::copyLetterCase($adjective, $result);
        }

        return $result;
    }

    protected function getMasculineStem(string $adjective): string
    {
        $length = mb_strlen($adjective);
        $lowerAdjective = mb_strtolower($adjective);
        
        // Handle soft adjectives ending in -ній
        if ($length >= 3 && mb_substr($lowerAdjective, -3) === 'ній') {
            return mb_substr($adjective, 0, -3);
        }
        
        // Handle regular adjectives ending in -ий or -ій
        if ($length >= 2) {
            $lastTwo = mb_substr($adjective, -2);
            if (mb_strtolower($lastTwo) === 'ий' || mb_strtolower($lastTwo) === 'ій') {
                return mb_substr($adjective, 0, -2);
            }
        }
        
        return mb_substr($adjective, 0, -2); // fallback
    }

    protected function getFeminineStem(string $adjective): string
    {
        $lastChar = mb_substr($adjective, -1);
        if ($lastChar === 'а' || $lastChar === 'я') {
            return mb_substr($adjective, 0, -1);
        }
        
        return mb_substr($adjective, 0, -1); // fallback
    }

    protected function getNeuterStem(string $adjective): string
    {
        $lastChar = mb_substr($adjective, -1);
        if ($lastChar === 'е' || $lastChar === 'є') {
            return mb_substr($adjective, 0, -1);
        }
        
        return mb_substr($adjective, 0, -1); // fallback
    }

    protected function getPluralStem(string $adjective): string
    {
        $lastChar = mb_substr($adjective, -1);
        if ($lastChar === 'і' || $lastChar === 'ї') {
            return mb_substr($adjective, 0, -1);
        }
        
        return mb_substr($adjective, 0, -1); // fallback
    }
} 