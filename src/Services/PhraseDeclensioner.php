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



    protected function declineWordWithSpecialRules(string $word, GrammaticalCase $case, Number $number, Gender $gender): string
    {
        // Handle surnames ending in -енко in vocative case (they remain unchanged)
        if ($case === GrammaticalCase::VOCATIVE && $this->isSurnameEnko($word)) {
            return $word;
        }
        
        // For phrases, use regular declension to avoid applying surname/military rules to common words
        // Only apply special rules if the word is clearly a name or military rank in context
        if ($this->isObviousNameOrRank($word)) {
            $declined = $this->nounDeclensioner->decline($word, $case, $number, $gender);
        } else {
            // Use regular declension rules directly
            $declined = $this->declineWordRegularly($word, $case, $number, $gender);
        }
        
        // Preserve case when declining
        $isUppercase = $this->isWordUppercase($word);
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
        if (WordHelper::endsWith($lowerWord, 'енко') && $this->isWordUppercase($word)) {
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
            'командир', 'заступник', 'начальник', 'головний', 'старший', 'молодший',
            'оперативний', 'черговий', 'фельдшер', 'кухар', 'оператор', 'водій',
            'механік', 'стрілець', 'гранатометник', 'кулеметник', 'снайпер'
        ];
        
        // Military ranks
        $militaryRanks = [
            'сержант', 'старшина', 'лейтенант', 'капітан', 'майор', 'підполковник', 'полковник'
        ];
        
        // Check if first word is a position title or if second word is a military rank
        if (in_array($firstWord, $positionTitles) || in_array($secondWord, $militaryRanks)) {
            return true;
        }
        
        // Check if the phrase contains typical position description patterns
        $phraseText = implode(' ', $words);
        if (preg_match('/військової частини|роти|взводу|батареї|дивізіону/ui', $phraseText)) {
            return true;
        }
        
        return false;
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
} 