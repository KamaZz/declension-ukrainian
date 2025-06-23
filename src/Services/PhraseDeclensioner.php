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

        // Use universal approach for all multi-word phrases
        if (count($words) > 1) {
            return $this->declineMultiWordPhrase($words, $case, $number, $gender);
        }

        // Single word - decline normally
        return $this->declineWordWithSpecialRules($words[0], $case, $number, $gender);
    }

    protected function declineMultiWordPhrase(array $words, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        // For position descriptions, only decline the first 1-2 words (the actual position/rank)
        // The rest should remain unchanged as they're already in the correct grammatical form
        if ($this->isPositionDescription($words)) {
            return $this->declinePositionDescription($words, $case, $number, $gender);
        }
        
        // Check if this is a military rank with full name (e.g., "старший лейтенант ДЖУРЯК Іван Михайлович")
        if ($this->isMilitaryRankWithName($words)) {
            return $this->declineMilitaryRankWithName($words, $case, $number, $gender);
        }
        
        // For other phrases (names, etc.), use the original logic
        $declinedWords = [];
        
        foreach ($words as $index => $word) {
            // Skip words that should not be declined
            if ($this->shouldSkipDeclension($word)) {
                $declinedWords[] = $word;
            }
            // Check if this word should be treated as an adjective modifying the next word
            elseif ($index < count($words) - 1 && $this->isAdjective($word)) {
                $declinedWords[] = $this->adjectiveDeclensioner->decline($word, $case, $gender, $number, true);
            } else {
                $declinedWords[] = $this->declineWordWithSpecialRules($word, $case, $number, $gender);
            }
        }
        
        return implode(' ', $declinedWords);
    }

    protected function isAdjective(string $word): bool
    {
        $lowerWord = mb_strtolower($word);
        return mb_strlen($lowerWord) >= 2 && mb_substr($lowerWord, -2) === 'ий';
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



    protected function declineWordWithSpecialRules(string $word, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        // Handle surnames ending in -енко in vocative case (they remain unchanged)
        if ($case === GrammaticalCase::VOCATIVE && $this->isSurnameEnko($word)) {
            return $word;
        }
        
        // Always use the main declensioner which has all the special rules
        $declined = $this->nounDeclensioner->decline($word, $case, $number, $gender);
        
        // Preserve case when declining
        if ($declined !== $word) {
            return WordHelper::copyLetterCase($word, $declined);
        }
        
        return $declined;
    }

    protected function isSurnameEnko(string $word): bool
    {
        return WordHelper::endsWith(mb_strtolower($word), 'енко');
    }





    protected function shouldSkipDeclension(string $word): bool
    {
        // Numbers (digits)
        if (preg_match('/^\d+$/', $word)) {
            return true;
        }
        
        // Military unit codes (letters + numbers like А0000, B1234, etc.)
        if (preg_match('/^[A-ZА-Я]\d+$/', $word)) {
            return true;
        }
        
        // Very short words that are likely prepositions or particles
        if (mb_strlen($word) <= 2) {
            return true;
        }
        
        // Common prepositions and particles that shouldn't be declined
        $unchangeableWords = ['в', 'з', 'на', 'до', 'від', 'при', 'під', 'над', 'за', 'про', 'для', 'без', 'через', 'після', 'перед'];
        if (in_array(mb_strtolower($word), $unchangeableWords)) {
            return true;
        }
        
        return false;
    }

    protected function isObviousNameOrRank(string $word): bool
    {
        $lowerWord = mb_strtolower($word);
        
        // Clear military ranks
        $militaryRanks = ['капітан', 'майор', 'підполковник', 'полковник', 'лейтенант'];
        if (in_array($lowerWord, $militaryRanks)) {
            return true;
        }
        
        // Clear surname patterns (only very obvious ones)
        if (WordHelper::endsWith($lowerWord, 'енко') && WordHelper::isWordUppercase($word)) {
            return true;
        }
        
        return false;
    }

    protected function declineWordRegularly(string $word, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        // Call the regular declension method directly, bypassing special surname/military logic
        $reflection = new \ReflectionClass($this->nounDeclensioner);
        $method = $reflection->getMethod('declineRegularWord');
        $method->setAccessible(true);
        
        return $method->invoke($this->nounDeclensioner, $word, $case, $gender, $number);
    }

    protected function isPositionDescription(array $words): bool
    {
        // Check if this looks like a military/professional position description
        $firstWord = mb_strtolower($words[0] ?? '');
        $secondWord = mb_strtolower($words[1] ?? '');
        
        // Common position titles
        $positionTitles = [
            'командир', 'заступник', 'начальник', 'головний', 'оперативний', 'черговий', 
            'фельдшер', 'кухар', 'оператор', 'водій', 'механік', 'стрілець', 
            'гранатометник', 'кулеметник', 'снайпер'
        ];
        
        // Military ranks
        $militaryRanks = [
            'сержант', 'старшина', 'лейтенант', 'капітан', 'майор', 'підполковник', 'полковник'
        ];
        
        // Check if first word is a position title (but exclude "старший" and "молодший" when they're part of rank names)
        if (in_array($firstWord, $positionTitles)) {
            return true;
        }
        
        // Check if second word is a military rank and we don't have a name pattern
        if (in_array($secondWord, $militaryRanks)) {
            // Only consider it a position if it doesn't look like "rank + surname + name + patronymic"
            if (count($words) < 5 || !$this->hasNamePattern($words, 2)) {
                return true;
            }
        }
        
        // Check if the phrase contains typical position description patterns
        $phraseText = implode(' ', $words);
        if (preg_match('/військової частини|роти|взводу|батареї|дивізіону/ui', $phraseText)) {
            return true;
        }
        
        return false;
    }

    protected function hasNamePattern(array $words, int $startIndex): bool
    {
        if (count($words) < $startIndex + 3) {
            return false;
        }
        
        $surname = $words[$startIndex] ?? '';
        $firstName = $words[$startIndex + 1] ?? '';
        $patronymic = $words[$startIndex + 2] ?? '';
        
        return WordHelper::isWordUppercase($surname) &&
               $this->isFirstName($firstName) &&
               $this->isPatronymic($patronymic);
    }

    protected function declinePositionDescription(array $words, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        $declinedWords = [];
        
        foreach ($words as $index => $word) {
            // Skip military unit codes and numbers
            if ($this->shouldSkipDeclension($word)) {
                $declinedWords[] = $word;
                continue;
            }
            
            // Only decline the first 1-2 words (the actual position/rank)
            if ($index <= 1 && $this->shouldDeclineInPosition($word, $index, $words)) {
                if ($this->isAdjective($word)) {
                    $declinedWords[] = $this->adjectiveDeclensioner->decline($word, $case, $gender, $number, true);
                } else {
                    $declinedWords[] = $this->declineWordRegularly($word, $case, $number, $gender);
                }
            } else {
                // Keep the rest unchanged - they're already in the correct grammatical form
                $declinedWords[] = $word;
            }
        }
        
        return implode(' ', $declinedWords);
    }

    protected function shouldDeclineInPosition(string $word, int $index, array $words): bool
    {
        $lowerWord = mb_strtolower($word);
        
        // Always decline the first word if it's a position title
        if ($index === 0) {
            $positionTitles = [
                'командир', 'заступник', 'начальник', 'головний', 'старший', 'молодший',
                'оперативний', 'черговий', 'фельдшер', 'кухар', 'оператор', 'водій',
                'механік', 'стрілець', 'гранатометник', 'кулеметник', 'снайпер'
            ];
            return in_array($lowerWord, $positionTitles);
        }
        
        // Decline the second word if it's a military rank, adjective modifying the first word, or a position noun
        if ($index === 1) {
            $militaryRanks = ['сержант', 'старшина', 'лейтенант', 'капітан', 'майор', 'підполковник', 'полковник'];
            $positionNouns = ['черговий', 'оператор', 'механік', 'водій', 'стрілець', 'гранатометник', 'кулеметник', 'снайпер'];
            
            return in_array($lowerWord, $militaryRanks) || 
                   in_array($lowerWord, $positionNouns) || 
                   $this->isAdjective($word);
        }
        
        return false;
    }

    protected function isMilitaryRankWithName(array $words): bool
    {
        if (count($words) < 4) {
            return false;
        }

        // Check if first word or first two words form a military rank
        $firstWord = mb_strtolower($words[0]);
        $firstTwoWords = count($words) >= 2 ? mb_strtolower($words[0] . ' ' . $words[1]) : '';
        
        $singleWordRanks = ['підполковник', 'капітан', 'майор', 'лейтенант', 'сержант', 'солдат'];
        $twoWordRanks = [
            'старший лейтенант', 'молодший лейтенант', 'старший сержант', 
            'молодший сержант', 'головний сержант', 'штаб сержант',
            'майстер сержант', 'головний старшина', 'старший солдат'
        ];
        
        $isSingleWordRank = in_array($firstWord, $singleWordRanks);
        $isTwoWordRank = in_array($firstTwoWords, $twoWordRanks);
        
        if (!$isSingleWordRank && !$isTwoWordRank) {
            return false;
        }

        // Determine where the name starts (after rank)
        $nameStartIndex = $isTwoWordRank ? 2 : 1;
        
        // Check if we have surname + first name + patronymic pattern
        if (count($words) >= $nameStartIndex + 3) {
            $surname = $words[$nameStartIndex];
            $firstName = $words[$nameStartIndex + 1];
            $patronymic = $words[$nameStartIndex + 2];
            
            // Check if surname is uppercase and first name/patronymic have proper endings
            return WordHelper::isWordUppercase($surname) &&
                   $this->isFirstName($firstName) &&
                   $this->isPatronymic($patronymic);
        }

        return false;
    }

    protected function declineMilitaryRankWithName(array $words, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        $declinedWords = [];
        
        // Determine if this is a two-word rank
        $firstWord = mb_strtolower($words[0]);
        $firstTwoWords = count($words) >= 2 ? mb_strtolower($words[0] . ' ' . $words[1]) : '';
        $twoWordRanks = [
            'старший лейтенант', 'молодший лейтенант', 'старший сержант', 
            'молодший сержант', 'головний сержант', 'штаб сержант',
            'майстер сержант', 'головний старшина', 'старший солдат'
        ];
        $isTwoWordRank = in_array($firstTwoWords, $twoWordRanks);
        $nameStartIndex = $isTwoWordRank ? 2 : 1;
        
        foreach ($words as $index => $word) {
            if ($index === 0) {
                // Decline the first part of military rank
                if ($this->isAdjective($word)) {
                    $declinedWords[] = $this->adjectiveDeclensioner->decline($word, $case, $gender, $number, true);
                } else {
                    $declinedWords[] = $this->declineWordWithSpecialRules($word, $case, $number, $gender);
                }
            } elseif ($index === 1 && $isTwoWordRank) {
                // Decline the second part of military rank (only for two-word ranks)
                $declinedWords[] = $this->declineWordWithSpecialRules($word, $case, $number, $gender);
            } elseif ($index >= $nameStartIndex) {
                // Decline names (surname, first name, patronymic)
                // Check if this word is actually an adjective (e.g., СЛАБКИЙ)
                if ($this->isAdjective($word)) {
                    $declinedWords[] = $this->adjectiveDeclensioner->decline($word, $case, $gender, $number, true);
                } else {
                    $declinedWords[] = $this->declineWordWithSpecialRules($word, $case, $number, $gender);
                }
            } else {
                // For single word ranks, this handles the name parts
                // Check if this word is actually an adjective (e.g., СЛАБКИЙ)
                if ($this->isAdjective($word)) {
                    $declinedWords[] = $this->adjectiveDeclensioner->decline($word, $case, $gender, $number, true);
                } else {
                    $declinedWords[] = $this->declineWordWithSpecialRules($word, $case, $number, $gender);
                }
            }
        }
        
        return implode(' ', $declinedWords);
    }

    protected function isFirstName(string $word): bool
    {
        // Common Ukrainian first name patterns
        $lowerWord = mb_strtolower($word);
        
        // Male names often end with consonants, -ій, -ій, -ан, -ен, etc.
        // Female names often end with -а, -я, -ія
        $malePatterns = ['р', 'н', 'л', 'й', 'ій', 'ан', 'ен', 'он', 'ич', 'ко'];
        $femalePatterns = ['а', 'я', 'іа', 'ія', 'на', 'ла'];
        
        foreach (array_merge($malePatterns, $femalePatterns) as $pattern) {
            if (WordHelper::endsWith($lowerWord, $pattern)) {
                return true;
            }
        }
        
        // Accept any word that looks like a name (starts with uppercase)
        return WordHelper::isTitleCase($word);
    }

    protected function isPatronymic(string $word): bool
    {
        $lowerWord = mb_strtolower($word);
        return WordHelper::endsWith($lowerWord, ['ович', 'йович', 'івич', 'евич', 'івна', 'ївна', 'овна']);
    }
} 