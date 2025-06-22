<?php

namespace UkrainianDeclension\Contracts;

use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Number;

interface DeclensionRuleContract
{
    /**
     * Decline a word according to the specific rule set.
     *
     * @param string $word The word to decline.
     * @param GrammaticalCase $case The target grammatical case.
     * @param Number $number The target grammatical number.
     * @return string The declined word.
     */
    public function decline(string $word, GrammaticalCase $case, Number $number): string;
} 