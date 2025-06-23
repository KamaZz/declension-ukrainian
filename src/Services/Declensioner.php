<?php

namespace UkrainianDeclension\Services;

use UkrainianDeclension\Contracts\DeclensionerContract;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Contracts\DeclensionGroupIdentifierContract;
use UkrainianDeclension\Enums\Declension;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\Rules\FirstDeclensionRule;
use UkrainianDeclension\Rules\SecondDeclensionRule;
use UkrainianDeclension\Rules\ThirdDeclensionRule;
use UkrainianDeclension\Rules\FourthDeclensionRule;
use UkrainianDeclension\Exceptions\UnsupportedWordException;
use UkrainianDeclension\Utils\WordHelper;

class Declensioner implements DeclensionerContract
{
    protected DeclensionGroupIdentifierContract $identifier;
    protected ?PhraseDeclensioner $phraseDeclensioner = null;
    protected array $rules = [];

    public function __construct(DeclensionGroupIdentifierContract $identifier)
    {
        $this->identifier = $identifier;
    }

    public function setPhraseDeclensioner(PhraseDeclensioner $phraseDeclensioner): void
    {
        $this->phraseDeclensioner = $phraseDeclensioner;
    }

    /**
     * @inheritDoc
     */
    public function decline(string $word, GrammaticalCase $case, Number $number, ?Gender $gender = null): string
    {
        if ($this->phraseDeclensioner !== null && str_contains($word, ' ')) {
            return $this->phraseDeclensioner->decline($word, $case, $number, $gender);
        }

        if ($gender === null) {
            $gender = WordHelper::guessGender($word);
        }

        // Handle special cases before general declension
        if ($case === GrammaticalCase::VOCATIVE && $this->isSurnameEnko($word)) {
            return $word; // Surnames ending in -енко remain unchanged in vocative
        }
        
        // Handle Ukrainian surname declension patterns
        if ($this->isUkrainianSurname($word)) {
            return $this->declineUkrainianSurname($word, $case, $gender);
        }
        
        // Handle military ranks
        if ($this->isMilitaryRank($word)) {
            return $this->declineMilitaryRank($word, $case, $gender);
        }

        // Preserve case when declining
        $isUppercase = $this->isWordUppercase($word);
        $declined = $this->declineRegularWord($word, $case, $gender, $number);
        
        if ($isUppercase && $declined !== $word) {
            return $this->preserveUppercase($word, $declined);
        }
        
        return $declined;
    }

    protected function isSurnameEnko(string $word): bool
    {
        return WordHelper::endsWith(mb_strtolower($word), 'енко');
    }

    protected function isWordUppercase(string $word): bool
    {
        return mb_strtoupper($word) === $word && mb_strtolower($word) !== $word;
    }

    protected function preserveUppercase(string $original, string $declined): string
    {
        // If original word is all uppercase, make declined word uppercase too
        if ($this->isWordUppercase($original)) {
            return mb_strtoupper($declined);
        }
        return $declined;
    }

    protected function isUkrainianSurname(string $word): bool
    {
        $lowerWord = mb_strtolower($word);
        
        // Exclude common words that end in surname-like patterns but aren't surnames
        $commonWords = ['любов', 'основ', 'морков', 'здоров', 'групи', 'групе', 'групу', 'групою', 'групах'];
        if (in_array($lowerWord, $commonWords)) {
            return false;
        }
        
        // Only check for clear surname patterns, not general uppercase words
        $surnamePatterns = [
            'енко',   // Петренко, Коваленко
            'ський',  // Левицький  
            'цький',  // Українцький
            'ич',     // Петрович (patronymics used as surnames)
            'юк',     // Федорчук
            'як',     // Костюк
            'ук',     // Семенюк
            'ів',     // Петрів
        ];
        
        foreach ($surnamePatterns as $pattern) {
            if (WordHelper::endsWith($lowerWord, $pattern)) {
                return true;
            }
        }
        
        // Special handling for -ов/-ев endings (more restrictive)
        // Only if it's clearly a surname pattern AND uppercase (like СУЧКОВ, ПЕТРЕНКО)
        if ($this->isWordUppercase($word) && 
            (preg_match('/[аеиіоуяєї][чквгхт]ов$/ui', $lowerWord) || 
             preg_match('/[аеиіоуяєї][чквгхт]ев$/ui', $lowerWord) ||
             WordHelper::endsWith($lowerWord, 'енко'))) {
            return true;
        }
        
        return false;
    }

    protected function declineUkrainianSurname(string $word, GrammaticalCase $case, Gender $gender): string
    {
        $lowerWord = mb_strtolower($word);
        $isUppercase = $this->isWordUppercase($word);
        
        // Special handling for surnames ending in -енко
        if (WordHelper::endsWith($lowerWord, 'енко')) {
            // According to Ukrainian grammar, -енко surnames are indeclinable for women
            // and decline normally for men
            if ($gender === Gender::FEMININE) {
                return $word; // Indeclinable for women
            } else {
                // Decline normally for men
                $stem = mb_substr($word, 0, -4); // Remove -енко
                $result = match($case) {
                    GrammaticalCase::GENITIVE => $stem . 'енка',
                    GrammaticalCase::DATIVE => $stem . 'енку', 
                    GrammaticalCase::ACCUSATIVE => $stem . 'енка',
                    GrammaticalCase::INSTRUMENTAL => $stem . 'енком',
                    GrammaticalCase::LOCATIVE => $stem . 'енку',
                    GrammaticalCase::VOCATIVE => $word, // Unchanged
                    default => $word, // NOMINATIVE
                };
                return $isUppercase ? mb_strtoupper($result) : $result;
            }
        }
        
        // For surnames ending in -ов/-ев (like СУЧКОВ), use regular second declension
        if (preg_match('/[аеиіоуяєї][чквгхт]ов$/ui', $lowerWord) || preg_match('/[аеиіоуяєї][чквгхт]ев$/ui', $lowerWord)) {
            // Use regular declension for -ов/-ев surnames
            $declined = $this->declineRegularWord($word, $case, Gender::MASCULINE, Number::SINGULAR);
            return $isUppercase ? $this->preserveUppercase($word, $declined) : $declined;
        }
        
        // Fall back to regular declension for other surname patterns
        return $this->declineRegularWord($word, $case, $gender, Number::SINGULAR);
    }

    protected function declineRegularWord(string $word, GrammaticalCase $case, Gender $gender, Number $number): string
    {
        $declension_group = $this->identifier->identify($word, $gender);

        if ($declension_group === Declension::INDECLINABLE) {
            return $word;
        }

        $this->rules = [
            Declension::FIRST->value => new FirstDeclensionRule(),
            Declension::SECOND->value => new SecondDeclensionRule($gender),
            Declension::THIRD->value => new ThirdDeclensionRule(),
            Declension::FOURTH->value => new FourthDeclensionRule(),
        ];
        
        if (!isset($this->rules[$declension_group->value])) {
            throw new UnsupportedWordException("No declension rule found for group [{$declension_group->value}].");
        }
        
        return $this->rules[$declension_group->value]->decline($word, $case, $number);
    }

    protected function isMilitaryRank(string $word): bool
    {
        $lowerWord = mb_strtolower($word);
        
        $militaryRanks = [
            'рядовий', 'молодший сержант', 'сержант', 'старший сержант', 'головний сержант',
            'штаб-сержант', 'майстер-сержант', 'старшина', 'головний старшина',
            'лейтенант', 'старший лейтенант', 'капітан', 'майор', 'підполковник', 'полковник',
            'бригадний генерал', 'генерал-майор', 'генерал-лейтенант', 'генерал'
        ];
        
        return in_array($lowerWord, $militaryRanks);
    }

    protected function declineMilitaryRank(string $word, GrammaticalCase $case, Gender $gender): string
    {
        $lowerWord = mb_strtolower($word);
        $isUppercase = $this->isWordUppercase($word);
        
        // Special patterns for military ranks
        switch ($lowerWord) {
            case 'капітан':
                $result = match($case) {
                    GrammaticalCase::GENITIVE => 'капітана',
                    GrammaticalCase::DATIVE => 'капітану',
                    GrammaticalCase::ACCUSATIVE => 'капітана',
                    GrammaticalCase::INSTRUMENTAL => 'капітаном',
                    GrammaticalCase::LOCATIVE => 'капітану', // Special: -у instead of -ові
                    GrammaticalCase::VOCATIVE => 'капітане',
                    default => $word, // NOMINATIVE
                };
                break;
                
            case 'майор':
                $result = match($case) {
                    GrammaticalCase::GENITIVE => 'майора',
                    GrammaticalCase::DATIVE => 'майору',
                    GrammaticalCase::ACCUSATIVE => 'майора',
                    GrammaticalCase::INSTRUMENTAL => 'майором',
                    GrammaticalCase::LOCATIVE => 'майору', // Special: -у instead of -ові
                    GrammaticalCase::VOCATIVE => 'майоре',
                    default => $word, // NOMINATIVE
                };
                break;
                
            case 'підполковник':
                $result = match($case) {
                    GrammaticalCase::GENITIVE => 'підполковника',
                    GrammaticalCase::DATIVE => 'підполковнику',
                    GrammaticalCase::ACCUSATIVE => 'підполковника',
                    GrammaticalCase::INSTRUMENTAL => 'підполковником',
                    GrammaticalCase::LOCATIVE => 'підполковнику', // Special: -у instead of -ові
                    GrammaticalCase::VOCATIVE => 'підполковнику',
                    default => $word, // NOMINATIVE
                };
                break;
                
            default:
                // For other ranks, fall back to regular declension
                return $this->declineRegularWord($word, $case, $gender, Number::SINGULAR);
        }
        
        return $isUppercase ? mb_strtoupper($result) : $result;
    }
}