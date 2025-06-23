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

        $result = match ($case) {
            GrammaticalCase::NOMINATIVE => $adjective,
            GrammaticalCase::GENITIVE => $stem . 'ого',
            GrammaticalCase::DATIVE => $stem . 'ому',
            GrammaticalCase::ACCUSATIVE => $isAnimate ? $stem . 'ого' : $adjective,
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
        if (mb_substr($adjective, -2) === 'ий') {
            return mb_substr($adjective, 0, -2);
        }
        if (mb_substr($adjective, -2) === 'ій') {
            return mb_substr($adjective, 0, -2);
        }
        
        return mb_substr($adjective, 0, -2); // fallback
    }

    protected function getFeminineStem(string $adjective): string
    {
        if (mb_substr($adjective, -1) === 'а') {
            return mb_substr($adjective, 0, -1);
        }
        if (mb_substr($adjective, -1) === 'я') {
            return mb_substr($adjective, 0, -1);
        }
        
        return mb_substr($adjective, 0, -1); // fallback
    }

    protected function getNeuterStem(string $adjective): string
    {
        if (mb_substr($adjective, -1) === 'е') {
            return mb_substr($adjective, 0, -1);
        }
        if (mb_substr($adjective, -1) === 'є') {
            return mb_substr($adjective, 0, -1);
        }
        
        return mb_substr($adjective, 0, -1); // fallback
    }

    protected function getPluralStem(string $adjective): string
    {
        if (mb_substr($adjective, -1) === 'і') {
            return mb_substr($adjective, 0, -1);
        }
        if (mb_substr($adjective, -1) === 'ї') {
            return mb_substr($adjective, 0, -1);
        }
        
        return mb_substr($adjective, 0, -1); // fallback
    }
} 