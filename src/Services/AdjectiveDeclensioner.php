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
        $stem = mb_substr($adjective, 0, -2); // for -ий

        return match ($case) {
            GrammaticalCase::NOMINATIVE => $adjective,
            GrammaticalCase::GENITIVE => $stem . 'ого',
            GrammaticalCase::DATIVE => $stem . 'ому',
            GrammaticalCase::ACCUSATIVE => $isAnimate ? $stem . 'ого' : $adjective,
            GrammaticalCase::INSTRUMENTAL => $stem . 'им',
            GrammaticalCase::LOCATIVE => $stem . 'ому',
            GrammaticalCase::VOCATIVE => $adjective,
        };
    }

    protected function declineFeminine(string $adjective, GrammaticalCase $case): string
    {
        if ($case === GrammaticalCase::NOMINATIVE || $case === GrammaticalCase::VOCATIVE) {
            return $adjective;
        }

        $stem = mb_substr($adjective, 0, -1); // for -а

        return match ($case) {
            GrammaticalCase::GENITIVE => $stem . 'ої',
            GrammaticalCase::DATIVE => $stem . 'ій',
            GrammaticalCase::ACCUSATIVE => $stem . 'у',
            GrammaticalCase::INSTRUMENTAL => $stem . 'ою',
            GrammaticalCase::LOCATIVE => $stem . 'ій',
        };
    }

    protected function declineNeuter(string $adjective, GrammaticalCase $case): string
    {
        if ($case === GrammaticalCase::NOMINATIVE || $case === GrammaticalCase::ACCUSATIVE || $case === GrammaticalCase::VOCATIVE) {
            return $adjective;
        }

        $stem = mb_substr($adjective, 0, -1); // for -е

        return match ($case) {
            GrammaticalCase::GENITIVE => $stem . 'ого',
            GrammaticalCase::DATIVE => $stem . 'ому',
            GrammaticalCase::INSTRUMENTAL => $stem . 'им',
            GrammaticalCase::LOCATIVE => $stem . 'ому',
        };
    }

    protected function declinePlural(string $adjective, GrammaticalCase $case, bool $isAnimate): string
    {
        if ($case === GrammaticalCase::NOMINATIVE || $case === GrammaticalCase::VOCATIVE) {
            return $adjective;
        }

        $stem = mb_substr($adjective, 0, -1); // for -і

        return match ($case) {
            GrammaticalCase::GENITIVE => $stem . 'их',
            GrammaticalCase::DATIVE => $stem . 'им',
            GrammaticalCase::ACCUSATIVE => $isAnimate ? $stem . 'их' : $adjective,
            GrammaticalCase::INSTRUMENTAL => $stem . 'ими',
            GrammaticalCase::LOCATIVE => $stem . 'их',
        };
    }
} 