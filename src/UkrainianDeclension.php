<?php

namespace UkrainianDeclension;

use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\Services\AdjectiveDeclensioner;
use UkrainianDeclension\Services\Declensioner;
use UkrainianDeclension\Services\DeclensionGroupIdentifier;
use UkrainianDeclension\Services\PhraseDeclensioner;

class UkrainianDeclension
{
    public static function decline(string $phrase, GrammaticalCase $case, Number $number, ?Gender $gender = null): string
    {
        $identifier = new DeclensionGroupIdentifier();
        $nounDeclensioner = new Declensioner($identifier);
        $adjectiveDeclensioner = new AdjectiveDeclensioner();
        $phraseDeclensioner = new PhraseDeclensioner($nounDeclensioner, $adjectiveDeclensioner);

        $nounDeclensioner->setPhraseDeclensioner($phraseDeclensioner);

        return $nounDeclensioner->decline($phrase, $case, $number, $gender);
    }
} 