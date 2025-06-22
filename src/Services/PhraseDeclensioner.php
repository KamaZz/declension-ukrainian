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

    public function decline(string $phrase, GrammaticalCase $case, Number $number, ?Gender $gender = null): string
    {
        $words = explode(' ', $phrase);
        $declinedWords = [];

        if ($gender === null) {
            $gender = $this->guessGenderForPhrase($words);
        }

        foreach ($words as $word) {
            $declinedWords[] = $this->nounDeclensioner->decline($word, $case, $number, $gender);
        }

        return implode(' ', $declinedWords);
    }

    protected function guessGenderForPhrase(array $words): Gender
    {
        foreach ($words as $word) {
            if (WordHelper::endsWith(mb_strtolower($word), 'ович')) {
                return Gender::MASCULINE;
            }
            if (WordHelper::endsWith(mb_strtolower($word), ['івна', 'ївна'])) {
                return Gender::FEMININE;
            }
        }

        // Try to guess by the last word, assuming it might be a name.
        if (!empty($words)) {
            $lastWord = end($words);
            $gender = WordHelper::guessGender($lastWord);
            if ($gender === Gender::FEMININE) {
                return Gender::FEMININE;
            }
        }

        return Gender::MASCULINE;
    }

    protected function isAdjective(string $word): bool
    {
        return WordHelper::endsWith($word, ['ий', 'а', 'е', 'і']);
    }
} 