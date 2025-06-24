<?php

namespace UkrainianDeclension\Contracts;

use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;

/**
 * Interface for the declension service.
 */
interface DeclensionerContract
{
    /**
     * Decline a word to a specific grammatical case and number.
     *
     * @param string $word The word to decline.
     * @param GrammaticalCase $case The target grammatical case.
     * @param Number $number The target grammatical number (singular or plural).
     * @param Gender|null $gender The gender of the word (optional).
     * @return string The declined word.
     */
    public function decline(string $word, GrammaticalCase $case, Number $number, ?Gender $gender = null): string;
} 