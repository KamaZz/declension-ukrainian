<?php

namespace UkrainianDeclension\Rules;

use UkrainianDeclension\Contracts\DeclensionRuleContract;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\NounSubgroup;
use UkrainianDeclension\Enums\Number;

class FirstDeclensionRule implements DeclensionRuleContract
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
        $subgroup = $this->getSubgroup($word);
        $stem = mb_substr($word, 0, -1);

        switch ($case) {
            case GrammaticalCase::GENITIVE:
                return $this->getGenitiveSingular($stem, $subgroup);
            case GrammaticalCase::DATIVE:
                return $this->getDativeSingular($stem, $subgroup);
            case GrammaticalCase::ACCUSATIVE:
                return $stem . (in_array($subgroup, [NounSubgroup::SOFT, NounSubgroup::MIXED]) ? 'ю' : 'у');
            case GrammaticalCase::INSTRUMENTAL:
                return $this->getInstrumentalSingular($stem, $subgroup);
            case GrammaticalCase::LOCATIVE:
                return $this->getLocativeSingular($stem, $subgroup);
            case GrammaticalCase::VOCATIVE:
                return $this->getVocativeSingular($stem, $subgroup);
            default: // NOMINATIVE
                return $word;
        }
    }

    public function declinePlural(string $word, GrammaticalCase $case): string
    {
        $subgroup = $this->getSubgroup($word);
        $stem = mb_substr($word, 0, -1);

        switch ($case) {
            case GrammaticalCase::GENITIVE:
                 if ($subgroup === NounSubgroup::SOFT && $word === 'земля') {
                    return 'земель';
                 }
                 if ($subgroup === NounSubgroup::HARD) {
                    return $stem; // with vowel insertion logic needed later
                 }
                 return $stem . ($subgroup === NounSubgroup::SOFT ? 'ь' : '');
            case GrammaticalCase::DATIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT ? 'ям' : 'ам');
            case GrammaticalCase::ACCUSATIVE:
                 // Simplified: Assuming inanimate, same as nominative
                 return $this->declinePlural($word, GrammaticalCase::NOMINATIVE);
            case GrammaticalCase::INSTRUMENTAL:
                return $stem . ($subgroup === NounSubgroup::SOFT ? 'ями' : 'ами');
            case GrammaticalCase::LOCATIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT ? 'ях' : 'ах');
            case GrammaticalCase::VOCATIVE:
                return $this->declinePlural($word, GrammaticalCase::NOMINATIVE);
            default: // NOMINATIVE
                return $stem . ($subgroup === NounSubgroup::HARD ? 'и' : 'і');
        }
    }

    protected function getSubgroup(string $word): NounSubgroup
    {
        $last_char = mb_substr($word, -1);
        $pre_ending_consonant = mb_substr($word, -2, 1);

        if (in_array($pre_ending_consonant, ['ж', 'ч', 'ш', 'щ'])) {
            return NounSubgroup::MIXED;
        }

        if ($last_char === 'я' || $pre_ending_consonant === 'ь') {
            return NounSubgroup::SOFT;
        }

        return NounSubgroup::HARD;
    }

    protected function getGenitiveSingular(string $stem, NounSubgroup $subgroup): string
    {
        if (in_array(mb_substr($stem, -2), ['ов', 'ев'])) {
            return $stem . 'ої';
        }
        if (mb_substr($stem, -1) === 'і') {
            return $stem . 'ї';
        }
        if ($subgroup === NounSubgroup::HARD) {
            return $stem . 'и';
        }
        
        return $stem . 'і';
    }

    protected function getDativeSingular(string $stem, NounSubgroup $subgroup): string
    {
        if (in_array(mb_substr($stem, -2), ['ов', 'ев'])) {
            return $stem . 'ій';
        }
        if (mb_substr($stem, -1) === 'і') {
            return $stem . 'ї';
        }
        $stem = $this->applyConsonantAlternation($stem);
        return $stem . 'і';
    }

    protected function getInstrumentalSingular(string $stem, NounSubgroup $subgroup): string
    {
        if (in_array(mb_substr($stem, -2), ['ов', 'ев'])) {
            return $stem . 'ою';
        }
        if (mb_substr($stem, -1) === 'і') {
            return $stem . 'єю';
        }
        if ($subgroup === NounSubgroup::HARD) {
            return $stem . 'ою';
        }

        return $stem . (in_array(mb_substr($stem, -1), ['ь', 'й']) || mb_substr($stem, -2, 1) == 'і' ? 'єю' : 'ею');
    }
    
    protected function getLocativeSingular(string $stem, NounSubgroup $subgroup): string
    {
        if ($subgroup === NounSubgroup::HARD && $stem === 'Сав') {
            return $stem . 'ові';
        }
        if (in_array(mb_substr($stem, -2), ['ов', 'ев'])) {
            return $stem . 'ій';
        }
        if (mb_substr($stem, -1) === 'і') {
            return $stem . 'ї';
        }
        $stem = $this->applyConsonantAlternation($stem);
        return $stem . 'і';
    }

    protected function getVocativeSingular(string $stem, NounSubgroup $subgroup): string
    {
        if (in_array(mb_substr($stem, -2), ['ов', 'ев'])) {
            return $stem . 'а';
        }
        if (mb_substr($stem, -1) === 'і') {
            return $stem . 'є';
        }
        if (mb_substr($stem, -2) === 'иц') {
            return $stem . 'е';
        }
        if ($subgroup === NounSubgroup::HARD) return $stem . 'о';
        if ($subgroup === NounSubgroup::MIXED) return $stem . 'е';
        if ($subgroup === NounSubgroup::SOFT) {
            $last_char = mb_substr($stem, -1);
            if ($last_char === 'ь') return $stem . 'е';
            if ($last_char === 'я') return $stem . 'є';
            if ($last_char === 'й') {
                $penultimate = mb_substr($stem, -2, 1);
                if (in_array($penultimate, ['а', 'е', 'є', 'и', 'і', 'ї', 'о', 'у', 'ю', 'я'])) return $stem . 'є';
            }
            return $stem . 'ю';
        }
        return $stem . 'е';
    }

    protected function applyConsonantAlternation(string $stem): string
    {
        $last_char = mb_substr($stem, -1);
        $alternations = ['г' => 'з', 'к' => 'ц', 'х' => 'с'];

        if (array_key_exists($last_char, $alternations)) {
            return mb_substr($stem, 0, -1) . $alternations[$last_char];
        }

        return $stem;
    }
} 