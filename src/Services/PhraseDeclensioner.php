<?php

namespace UkrainianDeclension\Services;

use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\Utils\WordHelper;

class PhraseDeclensioner
{
    protected Declensioner $nounDeclensioner;
    protected AdjectiveDeclensioner $adjectiveDeclensioner;

    public function __construct(Declensioner $nounDeclensioner, AdjectiveDeclensioner $adjectiveDeclensioner)
    {
        $this->nounDeclensioner = $nounDeclensioner;
        $this->adjectiveDeclensioner = $adjectiveDeclensioner;
    }

    public function decline(string $phrase, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        $words = explode(' ', $phrase);
        
        $head = [];
        $rest = [];

        if (WordHelper::endsWith($words[0], 'ий')) {
            $head = array_slice($words, 0, 2);
            $rest = array_slice($words, 2);
        } else {
            $head = array_slice($words, 0, 1);
            $rest = array_slice($words, 1);
        }

        $declinedHead = [];
        if (count($head) === 2) {
            $declinedHead[] = $this->adjectiveDeclensioner->decline($head[0], $case, $gender, $number, true);
            $declinedHead[] = $this->nounDeclensioner->decline($head[1], $case, $number, $gender);
        } else {
            $declinedHead[] = $this->nounDeclensioner->decline($head[0], $case, $number, $gender);
        }

        return implode(' ', array_merge($declinedHead, $rest));
    }

    protected function isAdjective(string $word): bool
    {
        return WordHelper::endsWith($word, ['ий', 'а', 'е', 'і']);
    }
} 