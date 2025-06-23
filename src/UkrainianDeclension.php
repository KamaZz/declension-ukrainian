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
    // Cache service instances to avoid repeated object creation
    private static ?DeclensionGroupIdentifier $identifier = null;
    private static ?Declensioner $nounDeclensioner = null;
    private static ?AdjectiveDeclensioner $adjectiveDeclensioner = null;
    private static ?PhraseDeclensioner $phraseDeclensioner = null;

    public static function decline(string $phrase, GrammaticalCase $case, Number $number, ?Gender $gender = null): string
    {
        // Initialize services only once
        if (self::$identifier === null) {
            self::$identifier = new DeclensionGroupIdentifier();
        }
        
        if (self::$nounDeclensioner === null) {
            self::$nounDeclensioner = new Declensioner(self::$identifier);
        }
        
        if (self::$adjectiveDeclensioner === null) {
            self::$adjectiveDeclensioner = new AdjectiveDeclensioner();
        }
        
        if (self::$phraseDeclensioner === null) {
            self::$phraseDeclensioner = new PhraseDeclensioner(self::$nounDeclensioner, self::$adjectiveDeclensioner);
            self::$nounDeclensioner->setPhraseDeclensioner(self::$phraseDeclensioner);
        }

        return self::$nounDeclensioner->decline($phrase, $case, $number, $gender);
    }
} 