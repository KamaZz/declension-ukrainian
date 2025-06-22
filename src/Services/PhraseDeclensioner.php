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

        if ($gender === null) {
            $gender = $this->guessGenderForPhrase($words) ?? Gender::MASCULINE;
        }

        if ($this->isFullName($words)) {
            return $this->declineFullName($words, $case, $number, $gender);
        }

        $head = [];
        $rest = [];

        if ($this->isAdjective($words[0])) {
            $head = array_slice($words, 0, 2);
            $rest = array_slice($words, 2);
        } else {
            $head = array_slice($words, 0, 1);
            $rest = array_slice($words, 1);
        }

        $declinedHead = [];
        if (count($head) > 1 && $this->isAdjective($head[0])) {
            $declinedHead[] = $this->adjectiveDeclensioner->decline($head[0], $case, $gender, $number, true);
            $declinedHead[] = $this->nounDeclensioner->decline($head[1], $case, $number, $gender);
        } else {
            $declinedHead[] = $this->nounDeclensioner->decline($head[0], $case, $number, $gender);
        }

        return implode(' ', array_merge($declinedHead, $rest));
    }

    protected function isAdjective(string $word): bool
    {
        return WordHelper::endsWith(mb_strtolower($word), 'ий');
    }

    protected function isFullName(array $words): bool
    {
        foreach ($words as $word) {
            if (WordHelper::endsWith(mb_strtolower($word), ['ович', 'йович', 'івна', 'ївна'])) {
                return true;
            }
        }
        return false;
    }

    protected function guessGenderForPhrase(array $words): ?Gender
    {
        foreach ($words as $word) {
            if (WordHelper::endsWith(mb_strtolower($word), ['ович', 'йович'])) {
                return Gender::MASCULINE;
            }
            if (WordHelper::endsWith(mb_strtolower($word), ['івна', 'ївна'])) {
                return Gender::FEMININE;
            }
        }

        if (!empty($words)) {
            $lastWord = end($words);
            return WordHelper::guessGender($lastWord);
        }

        return null;
    }

    protected function declineFullName(array $words, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        $declinedWords = [];
        
        foreach ($words as $index => $word) {
            // Handle surnames ending in -енко in vocative case (they remain unchanged)
            if ($case === GrammaticalCase::VOCATIVE && $this->isSurnameEnko($word)) {
                $declinedWords[] = $word;
            }
            // Handle first names ending in -ій in locative case (special pattern)
            elseif ($case === GrammaticalCase::LOCATIVE && $this->isNameEndingInIj($word) && $index === 1) {
                $declinedWords[] = $this->declineNameEndingInIjLocative($word);
            }
            // Regular declension for all other cases
            else {
                $declinedWords[] = $this->nounDeclensioner->decline($word, $case, $number, $gender);
            }
        }
        
        return implode(' ', $declinedWords);
    }

    protected function isSurnameEnko(string $word): bool
    {
        return WordHelper::endsWith(mb_strtolower($word), 'енко');
    }

    protected function isNameEndingInIj(string $word): bool
    {
        return WordHelper::endsWith(mb_strtolower($word), 'ій');
    }

    protected function declineNameEndingInIjLocative(string $word): string
    {
        // Names ending in -ій get -ієві in locative case (e.g., Сергій → Сергієві)
        $stem = mb_substr($word, 0, -2); // Remove -ій
        return $stem . 'ієві';
    }
} 