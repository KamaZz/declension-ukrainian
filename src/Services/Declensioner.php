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
    
    // Cache for rule objects to avoid repeated instantiation
    private static array $ruleCache = [];
    
    // Static arrays for better performance
    private const COMMON_WORDS_EXCLUSIONS = ['любов', 'основ', 'морков', 'здоров', 'групи', 'групе', 'групу', 'групою', 'групах'];
    private const SURNAME_PATTERNS = ['енко', 'ський', 'цький', 'ич', 'юк', 'як', 'ук', 'ів', 'ець', 'ха', 'ка', 'ін', 'ій'];
    private const VOWELS = ['а', 'е', 'и', 'і', 'о', 'у', 'я', 'є', 'ї', 'ю'];
    private const SHORT_NOUNS_WITH_U = [
        'сніг', 'сад', 'гай', 'дім', 'ліс', 'край', 'рік', 'час',
        'світ', 'дух', 'мир', 'шлях', 'бік', 'верх', 'низ', 'кінь'
    ];
    private const FIRST_NAME_PATTERNS = [
        'ан$', 'он$', 'ен$', 'ін$', 'ій$', 'ко$', 'ич$',
        'олександр$', 'володимир$', 'михайло$', 'іван$', 'петро$', 'сергій$',
        'андрій$', 'василь$', 'олексій$', 'дмитро$', 'максим$', 'артем$'
    ];
    private const MILITARY_RANKS = [
        'рядовий', 'молодший сержант', 'сержант', 'старший сержант', 'головний сержант',
        'штаб-сержант', 'майстер-сержант', 'старшина', 'головний старшина',
        'лейтенант', 'старший лейтенант', 'капітан', 'майор', 'підполковник', 'полковник',
        'бригадний генерал', 'генерал-майор', 'генерал-лейтенант', 'генерал',
        'солдат', 'старший солдат'
    ];

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

        // Cache lowercase version to avoid repeated calls
        $lowerWord = mb_strtolower($word);

        // Handle special cases before general declension
        // (removed -енко vocative special case - they should be declined normally)
        
        // Handle Ukrainian surname declension patterns
        if ($this->isUkrainianSurname($word, $lowerWord)) {
            return $this->declineUkrainianSurname($word, $lowerWord, $case, $gender);
        }
        
        // Handle military ranks
        if ($this->isMilitaryRank($lowerWord)) {
            return $this->declineMilitaryRank($word, $lowerWord, $case, $gender);
        }

        // Preserve case when declining
        $declined = $this->declineRegularWord($word, $case, $gender, $number);
        
        if ($declined !== $word) {
            return WordHelper::copyLetterCase($word, $declined);
        }
        
        return $declined;
    }

    protected function isSurnameEnko(string $lowerWord): bool
    {
        return WordHelper::endsWith($lowerWord, 'енко');
    }

    protected function isUkrainianSurname(string $word, string $lowerWord): bool
    {
        // Exclude common words that end in surname-like patterns but aren't surnames
        if (in_array($lowerWord, self::COMMON_WORDS_EXCLUSIONS)) {
            return false;
        }
        
        // Only check for clear surname patterns, not general uppercase words
        foreach (self::SURNAME_PATTERNS as $pattern) {
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

    protected function declineUkrainianSurname(string $word, string $lowerWord, GrammaticalCase $case, Gender $gender): string
    {
        // According to Ukrainian grammar sources, most surnames follow regular declension patterns
        // with a few systematic rules for specific endings
        
        // 1. Surnames ending in -енко are indeclinable for women, decline normally for men
        if (WordHelper::endsWith($lowerWord, 'енко')) {
            if ($gender === Gender::FEMININE) {
                return $word; // Indeclinable for women
            }
            // For men, use regular second declension
            return $this->declineRegularWord($word, $case, Gender::MASCULINE, Number::SINGULAR);
        }
        
        // 2. Systematic rule: surnames ending in -ха undergo х → с mutation in dative/locative
        if (WordHelper::endsWith($lowerWord, 'ха')) {
            $stem = mb_substr($lowerWord, 0, -2); // Remove -ха
            $result = match($case) {
                GrammaticalCase::GENITIVE => $stem . 'хи',
                GrammaticalCase::DATIVE => $stem . 'сі',  // х → с mutation
                GrammaticalCase::ACCUSATIVE => $stem . 'ху',
                GrammaticalCase::INSTRUMENTAL => $stem . 'хою',
                GrammaticalCase::LOCATIVE => $stem . 'сі',  // х → с mutation
                GrammaticalCase::VOCATIVE => $stem . 'хо',
                default => $word, // NOMINATIVE
            };
            return WordHelper::copyLetterCase($word, $result);
        }
        
        // 3. Systematic rule: surnames ending in -ка undergo к → ц mutation in dative/locative
        if (WordHelper::endsWith($lowerWord, 'ка')) {
            $stem = mb_substr($lowerWord, 0, -2); // Remove -ка
            $result = match($case) {
                GrammaticalCase::GENITIVE => $stem . 'ки',
                GrammaticalCase::DATIVE => $stem . 'ці',  // к → ц mutation
                GrammaticalCase::ACCUSATIVE => $stem . 'ку',
                GrammaticalCase::INSTRUMENTAL => $stem . 'кою',
                GrammaticalCase::LOCATIVE => $stem . 'ці',  // к → ц mutation
                GrammaticalCase::VOCATIVE => $stem . 'ко',
                default => $word, // NOMINATIVE
            };
            return WordHelper::copyLetterCase($word, $result);
        }
        
        // 4. Systematic rule: surnames ending in -ін follow soft declension pattern
        if (WordHelper::endsWith($lowerWord, 'ін')) {
            $stem = mb_substr($lowerWord, 0, -1); // Remove -н, keep the -і
            $result = match($case) {
                GrammaticalCase::GENITIVE => $stem . 'на',
                GrammaticalCase::DATIVE => $stem . 'ну',
                GrammaticalCase::ACCUSATIVE => $stem . 'на',
                GrammaticalCase::INSTRUMENTAL => $stem . 'ним',
                GrammaticalCase::LOCATIVE => $stem . 'ні',
                GrammaticalCase::VOCATIVE => $stem . 'не',
                default => $word, // NOMINATIVE
            };
            return WordHelper::copyLetterCase($word, $result);
        }
        
        // 5. Systematic rule: surnames ending in -ій follow specific pattern
        if (WordHelper::endsWith($lowerWord, 'ій')) {
            $stem = mb_substr($lowerWord, 0, -1); // Remove only -й, keep the -і
            $result = match($case) {
                GrammaticalCase::GENITIVE => $stem . 'я',
                GrammaticalCase::DATIVE => $stem . 'ю',
                GrammaticalCase::ACCUSATIVE => $stem . 'я',
                GrammaticalCase::INSTRUMENTAL => $stem . 'єм',
                GrammaticalCase::LOCATIVE => $stem . 'єві',
                GrammaticalCase::VOCATIVE => $stem . 'ю',
                default => $word, // NOMINATIVE
            };
            return WordHelper::copyLetterCase($word, $result);
        }
        
        // 6. All other Ukrainian surnames follow regular declension patterns
        return $this->declineRegularWord($word, $case, $gender, Number::SINGULAR);
    }

    protected function declineRegularWord(string $word, GrammaticalCase $case, Gender $gender, Number $number): string
    {
        $declension_group = $this->identifier->identify($word, $gender);

        if ($declension_group === Declension::INDECLINABLE) {
            return $word;
        }

        // Use cached rules to avoid repeated object creation
        $ruleKey = $declension_group->value . '_' . $gender->value;
        
        if (!isset(self::$ruleCache[$ruleKey])) {
            self::$ruleCache[$ruleKey] = match($declension_group) {
                Declension::FIRST => new FirstDeclensionRule(),
                Declension::SECOND => new SecondDeclensionRule($gender),
                Declension::THIRD => new ThirdDeclensionRule(),
                Declension::FOURTH => new FourthDeclensionRule(),
                default => throw new UnsupportedWordException("No declension rule found for group [{$declension_group->value}]."),
            };
        }
        
        return self::$ruleCache[$ruleKey]->decline($word, $case, $number);
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
        $syllableCount = $this->countSyllables($lowerWord);
        if ($syllableCount <= 2) {
            // Common short nouns that take -у
            if (in_array($lowerWord, self::SHORT_NOUNS_WITH_U)) {
                return 'у';
            }
        }
        
        // 3. Personal names often take -у (not -ові) unless they're clearly patronymics
        if ($this->isPersonalName($word, $lowerWord)) {
            return 'у';
        }
        
        // 4. Military ranks take -у 
        if ($this->isMilitaryRank($lowerWord)) {
            return 'у';
        }
        
        // 5. Default for animate masculine nouns: -ові/-і
        // But prefer -і for most cases to follow standard patterns
        return 'і';
    }

    protected function countSyllables(string $lowerWord): int
    {
        $count = 0;
        $length = mb_strlen($lowerWord);
        
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($lowerWord, $i, 1);
            if (in_array($char, self::VOWELS)) {
                $count++;
            }
        }
        
        return max(1, $count); // At least 1 syllable
    }

    protected function isPersonalName(string $word, string $lowerWord): bool
    {
        // Common Ukrainian first name patterns
        foreach (self::FIRST_NAME_PATTERNS as $pattern) {
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

    protected function isMilitaryRank(string $lowerWord): bool
    {
        return in_array($lowerWord, self::MILITARY_RANKS);
    }

    protected function declineMilitaryRank(string $word, string $lowerWord, GrammaticalCase $case, Gender $gender): string
    {
        // Special patterns for military ranks
        $result = match($lowerWord) {
            'капітан' => match($case) {
                GrammaticalCase::GENITIVE => 'капітана',
                GrammaticalCase::DATIVE => 'капітану',
                GrammaticalCase::ACCUSATIVE => 'капітана',
                GrammaticalCase::INSTRUMENTAL => 'капітаном',
                GrammaticalCase::LOCATIVE => 'капітанові',
                GrammaticalCase::VOCATIVE => 'капітане',
                default => $word, // NOMINATIVE
            },
            'майор' => match($case) {
                GrammaticalCase::GENITIVE => 'майора',
                GrammaticalCase::DATIVE => 'майору',
                GrammaticalCase::ACCUSATIVE => 'майора',
                GrammaticalCase::INSTRUMENTAL => 'майором',
                GrammaticalCase::LOCATIVE => 'майорові',
                GrammaticalCase::VOCATIVE => 'майоре',
                default => $word, // NOMINATIVE
            },
            'підполковник' => match($case) {
                GrammaticalCase::GENITIVE => 'підполковника',
                GrammaticalCase::DATIVE => 'підполковнику',
                GrammaticalCase::ACCUSATIVE => 'підполковника',
                GrammaticalCase::INSTRUMENTAL => 'підполковником',
                GrammaticalCase::LOCATIVE => 'підполковникові',
                GrammaticalCase::VOCATIVE => 'підполковнику',
                default => $word, // NOMINATIVE
            },
            'солдат' => match($case) {
                GrammaticalCase::GENITIVE => 'солдата',
                GrammaticalCase::DATIVE => 'солдату',
                GrammaticalCase::ACCUSATIVE => 'солдата',
                GrammaticalCase::INSTRUMENTAL => 'солдатом',
                GrammaticalCase::LOCATIVE => 'солдатові',
                GrammaticalCase::VOCATIVE => 'солдате',
                default => $word, // NOMINATIVE
            },
            'лейтенант' => match($case) {
                GrammaticalCase::GENITIVE => 'лейтенанта',
                GrammaticalCase::DATIVE => 'лейтенанту',
                GrammaticalCase::ACCUSATIVE => 'лейтенанта',
                GrammaticalCase::INSTRUMENTAL => 'лейтенантом',
                GrammaticalCase::LOCATIVE => 'лейтенантові',
                GrammaticalCase::VOCATIVE => 'лейтенанте',
                default => $word, // NOMINATIVE
            },
            'сержант' => match($case) {
                GrammaticalCase::GENITIVE => 'сержанта',
                GrammaticalCase::DATIVE => 'сержанту',
                GrammaticalCase::ACCUSATIVE => 'сержанта',
                GrammaticalCase::INSTRUMENTAL => 'сержантом',
                GrammaticalCase::LOCATIVE => 'сержантові',
                GrammaticalCase::VOCATIVE => 'сержанте',
                default => $word, // NOMINATIVE
            },
            default => null
        };
        
        if ($result !== null) {
            return WordHelper::copyLetterCase($word, $result);
        }
        
        // For other ranks, fall back to regular declension
        return $this->declineRegularWord($word, $case, $gender, Number::SINGULAR);
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