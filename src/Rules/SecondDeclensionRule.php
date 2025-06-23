<?php

namespace UkrainianDeclension\Rules;

use UkrainianDeclension\Contracts\DeclensionRuleContract;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\NounSubgroup;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\Utils\WordHelper;

class SecondDeclensionRule implements DeclensionRuleContract
{
    private Gender $gender;
    private bool $isAnimate;

    public function __construct(Gender $gender, bool $isAnimate = true)
    {
        $this->gender = $gender;
        $this->isAnimate = $isAnimate;
    }

    public function decline(string $word, GrammaticalCase $case, Number $number): string
    {
        if ($number === Number::PLURAL) {
            return $this->declinePlural($word, $case);
        }

        if ($this->gender === Gender::MASCULINE) {
            return $this->declineMasculineSingular($word, $case);
        }

        return $this->declineNeuterSingular($word, $case);
    }

    public function declineMasculineSingular(string $word, GrammaticalCase $case): string
    {
        if (WordHelper::endsWith($word, 'ий')) {
            $adjDeclensioner = new \UkrainianDeclension\Services\AdjectiveDeclensioner();
            return $adjDeclensioner->decline($word, $case, Gender::MASCULINE, Number::SINGULAR, $this->isAnimate);
        }

        // Special handling for surnames ending in -ов/-ев (like СУЧКОВ, ПЕТРОВ, etc.)
        if (preg_match('/ов$/ui', $word) || preg_match('/ев$/ui', $word)) {
            // Additional check to ensure it's a surname pattern (not common words like "любов")
            $lowerWord = mb_strtolower($word);
            $commonWords = ['любов', 'основ', 'морков', 'здоров'];
            if (!in_array($lowerWord, $commonWords)) {
                return $this->declineSurnameOvEv($word, $case);
            }
        }

        $subgroup = $this->getSubgroup($word);
        $stem = $word;

        if (in_array(mb_substr($word, -1), ['о', 'е'])) {
            $stem = mb_substr($word, 0, -1);
        } else {
            $stem = WordHelper::getStem($word);
        }

        switch ($case) {
            case GrammaticalCase::GENITIVE:
                return $this->getGenitiveMasculineSingular($stem, $subgroup, $word);
            case GrammaticalCase::DATIVE:
                return $this->getDativeMasculineSingular($stem, $subgroup, $word);
            case GrammaticalCase::ACCUSATIVE:
                if ($this->isAnimate) {
                    return $this->declineMasculineSingular($word, GrammaticalCase::GENITIVE);
                }
                return $word;
            case GrammaticalCase::INSTRUMENTAL:
                if (in_array($subgroup, [NounSubgroup::SOFT, NounSubgroup::MIXED], true)) {
                    $last_char = mb_substr($stem, -1);
                    if ($last_char === 'і' && WordHelper::endsWith($word, 'ій')) {
                        return mb_substr($word, 0, -1) . 'єм';
                    }
                    if ($last_char === 'і') {
                        $stem = mb_substr($stem, 0, -1) . 'о';
                    }
                    if(WordHelper::endsWith($word, 'ець')){
                        return mb_substr($word, 0, -3) . 'цем';
                    }
                    return $stem . 'ем';
                }
                return $stem . 'ом';
            case GrammaticalCase::LOCATIVE:
                return $this->getLocativeSingular($stem, $subgroup, $word);
            case GrammaticalCase::VOCATIVE:
                return $this->getVocativeMasculineSingular($stem, $subgroup, $word);
            default: // NOMINATIVE
                return $word;
        }
    }

    public function declineNeuterSingular(string $word, GrammaticalCase $case): string
    {
        $subgroup = $this->getSubgroup($word);
        $stem = mb_substr($word, 0, -1);

        switch ($case) {
            case GrammaticalCase::GENITIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT ? 'я' : 'а');
            case GrammaticalCase::DATIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT ? 'ю' : 'у');
            case GrammaticalCase::ACCUSATIVE:
                return $word;
            case GrammaticalCase::INSTRUMENTAL:
                return $stem . ($subgroup === NounSubgroup::SOFT || $subgroup === NounSubgroup::MIXED ? 'ем' : 'ом');
            case GrammaticalCase::LOCATIVE:
                return $this->getLocativeSingular($stem, $subgroup, $word);
            case GrammaticalCase::VOCATIVE:
                return $word;
            default: // NOMINATIVE
                return $word;
        }
    }

    public function declinePlural(string $word, GrammaticalCase $case): string
    {
        $subgroup = $this->getSubgroup($word);
        
        if ($this->gender === Gender::MASCULINE) {
            return $this->declineMasculinePlural($word, $case, $subgroup);
        }

        return $this->declineNeuterPlural($word, $case, $subgroup);
    }

    protected function declineMasculinePlural(string $word, GrammaticalCase $case, NounSubgroup $subgroup): string
    {
        $stem = WordHelper::getStem($word);
        
        switch ($case) {
            case GrammaticalCase::NOMINATIVE:
                if ($subgroup === NounSubgroup::HARD) return $stem . 'и';
                return $stem . 'і';
            case GrammaticalCase::GENITIVE:
                return $stem . 'ів';
            case GrammaticalCase::DATIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT || $subgroup === NounSubgroup::MIXED ? 'ям' : 'ам');
            case GrammaticalCase::ACCUSATIVE:
                if ($this->isAnimate) {
                    return $this->declineMasculinePlural($word, GrammaticalCase::GENITIVE, $subgroup);
                }
                return $this->declineMasculinePlural($word, GrammaticalCase::NOMINATIVE, $subgroup);
            case GrammaticalCase::INSTRUMENTAL:
                 if ($subgroup === NounSubgroup::SOFT || $subgroup === NounSubgroup::MIXED) {
                    return $stem . 'ями';
                }
                return $stem . 'ами';
            case GrammaticalCase::LOCATIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT || $subgroup === NounSubgroup::MIXED ? 'ях' : 'ах');
            case GrammaticalCase::VOCATIVE:
                 return $this->declineMasculinePlural($word, GrammaticalCase::NOMINATIVE, $subgroup);
            default: // NOMINATIVE
                 if ($subgroup === NounSubgroup::HARD) return $stem . 'и';
                 if ($subgroup === NounSubgroup::MIXED) return $stem . 'і';
                 return WordHelper::getStem($word) . 'і';
        }
    }
    
    protected function declineNeuterPlural(string $word, GrammaticalCase $case, NounSubgroup $subgroup): string
    {
        $stem = mb_substr($word, 0, -1);
        
        switch ($case) {
            case GrammaticalCase::GENITIVE:
                 if ($word === 'вікно') {
                    return 'вікон';
                 }
                 if (in_array(mb_substr($word, -2, 1), ['н', 'д', 'т'])) {
                     return $stem;
                 }
                 return $stem;
            case GrammaticalCase::DATIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT || $subgroup === NounSubgroup::MIXED ? 'ям' : 'ам');
            case GrammaticalCase::ACCUSATIVE:
                return $this->declineNeuterPlural($word, GrammaticalCase::NOMINATIVE, $subgroup);
            case GrammaticalCase::INSTRUMENTAL:
                return $stem . ($subgroup === NounSubgroup::SOFT || $subgroup === NounSubgroup::MIXED ? 'ями' : 'ами');
            case GrammaticalCase::LOCATIVE:
                return $stem . ($subgroup === NounSubgroup::SOFT || $subgroup === NounSubgroup::MIXED ? 'ях' : 'ах');
            case GrammaticalCase::VOCATIVE:
                return $this->declineNeuterPlural($word, GrammaticalCase::NOMINATIVE, $subgroup);
            default: // NOMINATIVE
                return $stem . ($subgroup === NounSubgroup::SOFT ? 'я' : 'а');
        }
    }

    protected function getSubgroup(string $word): NounSubgroup
    {
        $softExceptions = ['Кухар', 'Ігор', 'лікар', 'секретар', 'воротар'];
        if (in_array($word, $softExceptions, true)) {
            return NounSubgroup::SOFT;
        }

        $last_char = mb_substr($word, -1);
        $last_two = mb_substr($word, -2);

        if (in_array($last_char, ['ж', 'ч', 'ш', 'щ'], true)) {
            return NounSubgroup::MIXED;
        }

        // Only specific -р endings should be MIXED, not all consonant+р combinations
        if ($last_char === 'р' && in_array($last_two, ['тр', 'др', 'бр', 'пр', 'кр', 'гр'])) {
            return NounSubgroup::HARD; // Names like Олександр, Петр should be HARD
        }
        if ($last_char === 'р' && !in_array($last_two, ['ар', 'ор', 'ер', 'ир'])) {
            return NounSubgroup::MIXED;
        }

        if (in_array($last_char, ['ь', 'й'], true) || WordHelper::endsWith($word, 'ець') || ($last_two === 'ій') || ($last_char === 'р' && $this->gender === Gender::NEUTER)) {
             return NounSubgroup::SOFT;
        }
        
        if ($this->gender === Gender::NEUTER && $last_char === 'е') {
             $pre_ending_consonant = mb_substr($word, -2, 1);
             if (in_array($pre_ending_consonant, ['ж', 'ч', 'ш', 'щ'], true)) {
                return NounSubgroup::MIXED;
             }
             return NounSubgroup::SOFT;
        }

        if (WordHelper::endsWith($word, 'йович')) {
            return NounSubgroup::SOFT;
        }
        
        return NounSubgroup::HARD;
    }

    protected function getGenitiveMasculineSingular(string $stem, NounSubgroup $subgroup, string $word): string
    {
        if(WordHelper::endsWith($word, 'ець')){
            return mb_substr($word, 0, -3) . 'ця';
        }
        return $stem . ($subgroup === NounSubgroup::SOFT ? 'я' : 'а');
    }

    protected function getDativeMasculineSingular(string $stem, NounSubgroup $subgroup, string $word): string
    {
        if (WordHelper::endsWith($word, 'ович')) {
            return $stem . 'у';
        }
        if (WordHelper::endsWith($word, 'ець')) {
            return mb_substr($word, 0, -3) . 'цю';
        }
        return $stem . ($subgroup === NounSubgroup::SOFT ? 'ю' : 'у');
    }

    protected function getLocativeSingular(string $stem, NounSubgroup $subgroup, string $word): string
    {
        if (WordHelper::endsWith($word, 'ович')) {
            return $stem . 'у';
        }

        if ($this->gender === Gender::MASCULINE) {
            if (WordHelper::endsWith($word, 'енко')) {
                return $stem . 'у';
            }
            if (WordHelper::endsWith($word, 'ик') && $subgroup == NounSubgroup::HARD) {
                 return $stem . 'у';
            }
             if (WordHelper::endsWith($word, 'як') && $subgroup == NounSubgroup::HARD) {
                 return $stem . 'ові';
            }

            if ($subgroup === NounSubgroup::HARD) {
                return $stem . 'ові';
            }
            if ($subgroup === NounSubgroup::MIXED) {
                 if(in_array(mb_substr($stem, -1), ['ч'])){
                    return $stem . 'у';
                }
                return $stem . 'еві';
            }
            if ($subgroup === NounSubgroup::SOFT) {
                if (WordHelper::endsWith($word, 'ець')) {
                    return mb_substr($word, 0, -3) . 'цеві';
                }
                // Special case for names ending in -ій (e.g., Віталій → Віталієві, Сергій → Сергієві)
                if (WordHelper::endsWith($word, 'ій')) {
                    return mb_substr($word, 0, -2) . 'ієві';
                }
                $last_char_of_word = mb_substr($word, -1);
                if ($last_char_of_word === 'й') {
                    // Masculine nouns ending in -й take -ї in locative (e.g., трамвай → трамваї)
                    return $stem . 'ї';
                }
                if ($last_char_of_word === 'ь') {
                    return $stem . 'єві';
                }
                return $stem . 'еві';
            }
        }
        
        if ($this->gender === Gender::NEUTER) {
            return $stem . 'і';
        }

        return $stem . 'і';
    }

    protected function getVocativeMasculineSingular(string $stem, NounSubgroup $subgroup, string $word): string
    {
        if (WordHelper::endsWith($word, 'ович')) {
            return $stem . 'у';
        }
        if (WordHelper::endsWith($word, 'ець')) {
            return mb_substr($word, 0, -3) . 'цю';
        }
        if ($subgroup === NounSubgroup::MIXED) {
            $last_char_of_stem = mb_substr($stem, -1);
            if (in_array($last_char_of_stem, ['ч'])) {
                return $stem . 'у';
            }
            return $stem . 'е';
        }
        if ($subgroup === NounSubgroup::SOFT) {
            return $stem . 'ю';
        }
        if ($subgroup === NounSubgroup::HARD) {
            $last_char_of_stem = mb_substr($stem, -1);
            if (in_array($last_char_of_stem, ['г', 'к', 'х'], true)) {
                return $stem . 'у';
            }
            return $stem . 'е';
        }

        return $stem . 'е';
    }

    protected function declineSurnameOvEv(string $word, GrammaticalCase $case): string
    {
        // For surnames ending in -ов/-ев (like СУЧКОВ), the pattern is:
        // СУЧКОВ → СУЧКОВА/СУЧКОВУ/СУЧКОВИМ etc.
        // We need to replace the В with the appropriate ending
        $stem = mb_substr($word, 0, -1); // Remove the В: СУЧКОВ → СУЧКО
        
        switch ($case) {
            case GrammaticalCase::GENITIVE:
                return $stem . 'ва';
            case GrammaticalCase::DATIVE:
                return $stem . 'ву';
            case GrammaticalCase::ACCUSATIVE:
                return $stem . 'ва';
            case GrammaticalCase::INSTRUMENTAL:
                return $stem . 'вим';
            case GrammaticalCase::LOCATIVE:
                return $stem . 'ву';
            case GrammaticalCase::VOCATIVE:
                return $stem . 'ву';
            default: // NOMINATIVE
                return $word;
        }
    }
}