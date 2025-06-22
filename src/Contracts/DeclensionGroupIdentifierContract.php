<?php

namespace UkrainianDeclension\Contracts;

use UkrainianDeclension\Enums\Declension;
use UkrainianDeclension\Enums\Gender;

interface DeclensionGroupIdentifierContract
{
    /**
     * Identify the declension group of a noun.
     *
     * @param string $word The noun in its base form.
     * @param Gender $gender The gender of the noun.
     * @return Declension The identified declension group.
     */
    public function identify(string $word, Gender $gender): Declension;
} 