<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\Services\AdjectiveDeclensioner;

class AdjectiveDeclensionerTest extends TestCase
{
    #[DataProvider('adjectiveProvider')]
    public function testAdjectiveDeclension(string $adjective, GrammaticalCase $case, Gender $gender, Number $number, string $expected): void
    {
        $declensioner = new AdjectiveDeclensioner();
        $result = $declensioner->decline($adjective, $case, $gender, $number);
        $this->assertEquals($expected, $result);
    }

    public static function adjectiveProvider(): array
    {
        return [
            // Masculine
            'оперативний (genitive, masculine, singular)' => ['оперативний', GrammaticalCase::GENITIVE, Gender::MASCULINE, Number::SINGULAR, 'оперативного'],
            'оперативний (dative, masculine, singular)' => ['оперативний', GrammaticalCase::DATIVE, Gender::MASCULINE, Number::SINGULAR, 'оперативному'],
            'оперативний (instrumental, masculine, singular)' => ['оперативний', GrammaticalCase::INSTRUMENTAL, Gender::MASCULINE, Number::SINGULAR, 'оперативним'],
            'оперативний (locative, masculine, singular)' => ['оперативний', GrammaticalCase::LOCATIVE, Gender::MASCULINE, Number::SINGULAR, 'оперативному'],

            // Feminine - all these are correct according to Ukrainian grammar
            'оперативна (genitive, feminine, singular)' => ['оперативна', GrammaticalCase::GENITIVE, Gender::FEMININE, Number::SINGULAR, 'оперативної'],
            'оперативна (dative, feminine, singular)' => ['оперативна', GrammaticalCase::DATIVE, Gender::FEMININE, Number::SINGULAR, 'оперативній'],
            'оперативна (accusative, feminine, singular)' => ['оперативна', GrammaticalCase::ACCUSATIVE, Gender::FEMININE, Number::SINGULAR, 'оперативну'],
            'оперативна (instrumental, feminine, singular)' => ['оперативна', GrammaticalCase::INSTRUMENTAL, Gender::FEMININE, Number::SINGULAR, 'оперативною'],
            'оперативна (locative, feminine, singular)' => ['оперативна', GrammaticalCase::LOCATIVE, Gender::FEMININE, Number::SINGULAR, 'оперативній'],

            // Neuter - all these are correct according to Ukrainian grammar
            'оперативне (genitive, neuter, singular)' => ['оперативне', GrammaticalCase::GENITIVE, Gender::NEUTER, Number::SINGULAR, 'оперативного'],
            'оперативне (dative, neuter, singular)' => ['оперативне', GrammaticalCase::DATIVE, Gender::NEUTER, Number::SINGULAR, 'оперативному'],
            'оперативне (instrumental, neuter, singular)' => ['оперативне', GrammaticalCase::INSTRUMENTAL, Gender::NEUTER, Number::SINGULAR, 'оперативним'],
            'оперативне (locative, neuter, singular)' => ['оперативне', GrammaticalCase::LOCATIVE, Gender::NEUTER, Number::SINGULAR, 'оперативному'],

            // Plural - all these are correct according to Ukrainian grammar
            'оперативні (genitive, plural)' => ['оперативні', GrammaticalCase::GENITIVE, Gender::MASCULINE, Number::PLURAL, 'оперативних'], // Gender is ignored for plural
            'оперативні (dative, plural)' => ['оперативні', GrammaticalCase::DATIVE, Gender::MASCULINE, Number::PLURAL, 'оперативним'],
            'оперативні (instrumental, plural)' => ['оперативні', GrammaticalCase::INSTRUMENTAL, Gender::MASCULINE, Number::PLURAL, 'оперативними'],
            'оперативні (locative, plural)' => ['оперативні', GrammaticalCase::LOCATIVE, Gender::MASCULINE, Number::PLURAL, 'оперативних'],
        ];
    }
} 