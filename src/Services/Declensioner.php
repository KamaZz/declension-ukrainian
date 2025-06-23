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
        $declined = $this->declineRegularWord($word, $case, $gender, $number);
        
        if ($declined !== $word) {
            return WordHelper::copyLetterCase($word, $declined);
        }
        
        return $declined;
    }

    protected function isSurnameEnko(string $word): bool
    {
        return WordHelper::endsWith(mb_strtolower($word), 'енко');
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
        if (WordHelper::isWordUppercase($word) && 
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
        
        // Special handling for surnames ending in -енко
        if (WordHelper::endsWith($lowerWord, 'енко')) {
            // According to Ukrainian grammar, -енко surnames are indeclinable for women
            // and decline normally for men
            if ($gender === Gender::FEMININE) {
                return $word; // Indeclinable for women
            } else {
                // Decline normally for men
                $stem = mb_substr($lowerWord, 0, -4); // Remove -енко from lowercase
                $result = match($case) {
                    GrammaticalCase::GENITIVE => $stem . 'енка',
                    GrammaticalCase::DATIVE => $stem . 'енку', 
                    GrammaticalCase::ACCUSATIVE => $stem . 'енка',
                    GrammaticalCase::INSTRUMENTAL => $stem . 'енком',
                    GrammaticalCase::LOCATIVE => $stem . 'енку',
                    GrammaticalCase::VOCATIVE => $word, // Unchanged
                    default => $word, // NOMINATIVE
                };
                return WordHelper::copyLetterCase($word, $result);
            }
        }
        
        // For surnames ending in -ов/-ев (like СУЧКОВ), use regular second declension
        if (preg_match('/[аеиіоуяєї][чквгхт]ов$/ui', $lowerWord) || preg_match('/[аеиіоуяєї][чквгхт]ев$/ui', $lowerWord)) {
            // Use regular declension for -ов/-ев surnames
            $declined = $this->declineRegularWord($word, $case, Gender::MASCULINE, Number::SINGULAR);
            return WordHelper::copyLetterCase($word, $declined);
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

    protected function getLocativeEnding(string $word, Gender $gender): string
    {
        if ($gender !== Gender::MASCULINE) {
            return 'і'; // Standard ending for feminine/neuter
        }

        $lowerWord = mb_strtolower($word);
        
        // Ukrainian grammar rules for masculine locative case:
        
        // 1. Nouns ending in -к, -ак, -ук, -ок take -у
        if (preg_match('/[аеиіоуяєї]*[кг]$/u', $lowerWord) || 
            preg_match('/ак$/u', $lowerWord) || 
            preg_match('/ук$/u', $lowerWord) || 
            preg_match('/ок$/u', $lowerWord)) {
            return 'у';
        }
        
        // 2. Short nouns (1-2 syllables) often take -у
        $syllableCount = $this->countSyllables($word);
        if ($syllableCount <= 2) {
            // Common short nouns that take -у
            $shortNounsWithU = [
                'сніг', 'сад', 'гай', 'дім', 'ліс', 'край', 'рік', 'час',
                'світ', 'дух', 'мир', 'шлях', 'бік', 'верх', 'низ', 'кінь'
            ];
            
            if (in_array($lowerWord, $shortNounsWithU)) {
                return 'у';
            }
        }
        
        // 3. Personal names often take -у (not -ові) unless they're clearly patronymics
        if ($this->isPersonalName($word)) {
            return 'у';
        }
        
        // 4. Military ranks take -у 
        if ($this->isMilitaryRank($word)) {
            return 'у';
        }
        
        // 5. Default for animate masculine nouns: -ові/-і
        // But prefer -і for most cases to follow standard patterns
        return 'і';
    }

    protected function countSyllables(string $word): int
    {
        $vowels = ['а', 'е', 'и', 'і', 'о', 'у', 'я', 'є', 'ї', 'ю'];
        $count = 0;
        $lowerWord = mb_strtolower($word);
        
        for ($i = 0; $i < mb_strlen($lowerWord); $i++) {
            $char = mb_substr($lowerWord, $i, 1);
            if (in_array($char, $vowels)) {
                $count++;
            }
        }
        
        return max(1, $count); // At least 1 syllable
    }

    protected function isPersonalName(string $word): bool
    {
        $lowerWord = mb_strtolower($word);
        
        // Common Ukrainian first name patterns
        $firstNamePatterns = [
            'ан$', 'он$', 'ен$', 'ін$', 'ій$', 'ій$', 'ко$', 'ич$', 'ич$',
            'олександр$', 'володимир$', 'михайло$', 'іван$', 'петро$', 'сергій$',
            'андрій$', 'василь$', 'олексій$', 'дмитро$', 'максим$', 'артем$'
        ];
        
        foreach ($firstNamePatterns as $pattern) {
            if (preg_match('/' . $pattern . '/u', $lowerWord)) {
                return true;
            }
        }
        
        // Check if it's an uppercase word (likely surname)
        if (WordHelper::isWordUppercase($word)) {
            return true;
        }
        
        return false;
    }

    protected function isMilitaryRank(string $word): bool
    {
        $lowerWord = mb_strtolower($word);
        
        $militaryRanks = [
            'рядовий', 'молодший сержант', 'сержант', 'старший сержант', 'головний сержант',
            'штаб-сержант', 'майстер-сержант', 'старшина', 'головний старшина',
            'лейтенант', 'старший лейтенант', 'капітан', 'майор', 'підполковник', 'полковник',
            'бригадний генерал', 'генерал-майор', 'генерал-лейтенант', 'генерал',
            'солдат', 'старший солдат'
        ];
        
        return in_array($lowerWord, $militaryRanks);
    }

    protected function declineMilitaryRank(string $word, GrammaticalCase $case, Gender $gender): string
    {
        $lowerWord = mb_strtolower($word);
        
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
                
            case 'солдат':
                $result = match($case) {
                    GrammaticalCase::GENITIVE => 'солдата',
                    GrammaticalCase::DATIVE => 'солдату',
                    GrammaticalCase::ACCUSATIVE => 'солдата',
                    GrammaticalCase::INSTRUMENTAL => 'солдатом',
                    GrammaticalCase::LOCATIVE => 'солдату', // Special: -у instead of -ові
                    GrammaticalCase::VOCATIVE => 'солдате',
                    default => $word, // NOMINATIVE
                };
                break;
                
            case 'лейтенант':
                $result = match($case) {
                    GrammaticalCase::GENITIVE => 'лейтенанта',
                    GrammaticalCase::DATIVE => 'лейтенанту',
                    GrammaticalCase::ACCUSATIVE => 'лейтенанта',
                    GrammaticalCase::INSTRUMENTAL => 'лейтенантом',
                    GrammaticalCase::LOCATIVE => 'лейтенанту', // Special: -у instead of -ові
                    GrammaticalCase::VOCATIVE => 'лейтенанте',
                    default => $word, // NOMINATIVE
                };
                break;
                
            default:
                // For other ranks, fall back to regular declension
                return $this->declineRegularWord($word, $case, $gender, Number::SINGULAR);
        }
        
        return WordHelper::copyLetterCase($word, $result);
    }

    protected function getStem(string $word): string
    {
        $lowerWord = mb_strtolower($word);
        
        // Remove common endings to get stem
        if (mb_substr($lowerWord, -2) === 'ко') {
            return mb_substr($word, 0, -2);
        }
        if (mb_substr($lowerWord, -2) === 'ич') {
            return mb_substr($word, 0, -2);
        }
        if (mb_substr($lowerWord, -1) === 'ь') {
            return mb_substr($word, 0, -1);
        }
        if (mb_substr($lowerWord, -1) === 'й') {
            return mb_substr($word, 0, -1);
        }
        
        // For most masculine nouns, the stem is the word itself
        return $word;
    }
}