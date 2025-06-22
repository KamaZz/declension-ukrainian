<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\UkrainianDeclension;

class DeclensionerTest extends TestCase
{
    #[DataProvider('firstDeclensionNouns')]
    public function testFirstDeclension(string $word, GrammaticalCase $case, Number $number, string $expected): void
    {
        $result = UkrainianDeclension::decline($word, $case, $number, Gender::FEMININE);
        $this->assertEquals($expected, $result);
    }

    public static function firstDeclensionNouns(): array
    {
        return [
            // Singular
            'книга (genitive, singular)' => ['книга', GrammaticalCase::GENITIVE, Number::SINGULAR, 'книги'],
            'земля (dative, singular)' => ['земля', GrammaticalCase::DATIVE, Number::SINGULAR, 'землі'],
            'каша (instrumental, singular)' => ['каша', GrammaticalCase::INSTRUMENTAL, Number::SINGULAR, 'кашею'],
            // Plural
            'книга (nominative, plural)' => ['книга', GrammaticalCase::NOMINATIVE, Number::PLURAL, 'книги'],
            'земля (genitive, plural)' => ['земля', GrammaticalCase::GENITIVE, Number::PLURAL, 'земель'],
            'каша (dative, plural)' => ['каша', GrammaticalCase::DATIVE, Number::PLURAL, 'кашам'],
        ];
    }

    #[DataProvider('secondDeclensionNouns')]
    public function testSecondDeclension(string $word, Gender $gender, GrammaticalCase $case, Number $number, string $expected): void
    {
        $result = UkrainianDeclension::decline($word, $case, $number, $gender);
        $this->assertEquals($expected, $result);
    }

    public static function secondDeclensionNouns(): array
    {
        return [
            // Masculine Singular
            'стіл (genitive, singular)' => ['стіл', Gender::MASCULINE, GrammaticalCase::GENITIVE, Number::SINGULAR, 'стола'],
            'край (dative, singular)' => ['край', Gender::MASCULINE, GrammaticalCase::DATIVE, Number::SINGULAR, 'краю'],
            // Neuter Singular
            'вікно (instrumental, singular)' => ['вікно', Gender::NEUTER, GrammaticalCase::INSTRUMENTAL, Number::SINGULAR, 'вікном'],
            'море (locative, singular)' => ['море', Gender::NEUTER, GrammaticalCase::LOCATIVE, Number::SINGULAR, 'морі'],
            // Plural
            'стіл (nominative, plural)' => ['стіл', Gender::MASCULINE, GrammaticalCase::NOMINATIVE, Number::PLURAL, 'столи'],
            'вікно (genitive, plural)' => ['вікно', Gender::NEUTER, GrammaticalCase::GENITIVE, Number::PLURAL, 'вікон'],
        ];
    }

    #[DataProvider('thirdDeclensionNouns')]
    public function testThirdDeclension(string $word, GrammaticalCase $case, Number $number, string $expected): void
    {
        $result = UkrainianDeclension::decline($word, $case, $number, Gender::FEMININE);
        $this->assertEquals($expected, $result);
    }

    public static function thirdDeclensionNouns(): array
    {
        return [
            // Singular
            'ніч (genitive, singular)' => ['ніч', GrammaticalCase::GENITIVE, Number::SINGULAR, 'ночі'],
            'мати (instrumental, singular)' => ['мати', GrammaticalCase::INSTRUMENTAL, Number::SINGULAR, 'матір\'ю'],
            // Plural
            'ніч (nominative, plural)' => ['ніч', GrammaticalCase::NOMINATIVE, Number::PLURAL, 'ночі'],
            'мати (genitive, plural)' => ['мати', GrammaticalCase::GENITIVE, Number::PLURAL, 'матерів'],
        ];
    }

    #[DataProvider('fourthDeclensionNouns')]
    public function testFourthDeclension(string $word, GrammaticalCase $case, Number $number, string $expected): void
    {
        $result = UkrainianDeclension::decline($word, $case, $number, Gender::NEUTER);
        $this->assertEquals($expected, $result);
    }

    public static function fourthDeclensionNouns(): array
    {
        return [
            // Singular
            'теля (genitive, singular)' => ['теля', GrammaticalCase::GENITIVE, Number::SINGULAR, 'теляти'],
            'ім\'я (instrumental, singular)' => ['ім\'я', GrammaticalCase::INSTRUMENTAL, Number::SINGULAR, 'іменем'],
            // Plural
            'теля (nominative, plural)' => ['теля', GrammaticalCase::NOMINATIVE, Number::PLURAL, 'телята'],
            'ім\'я (genitive, plural)' => ['ім\'я', GrammaticalCase::GENITIVE, Number::PLURAL, 'імен'],
        ];
    }

    #[DataProvider('genderTestCases')]
    public function testGenderGuessing(string $word, GrammaticalCase $case, string $expected): void
    {
        $result = UkrainianDeclension::decline($word, $case, Number::SINGULAR);
        $this->assertEquals($expected, $result);
    }

    public static function genderTestCases(): array
    {
        return [
            // Masculine
            'стіл (genitive)' => ['стіл', GrammaticalCase::GENITIVE, 'стола'],
            'кінь (dative)' => ['кінь', GrammaticalCase::DATIVE, 'коню'],
            'трамвай (locative)' => ['трамвай', GrammaticalCase::LOCATIVE, 'трамваї'],

            // Feminine
            'книга (genitive)' => ['книга', GrammaticalCase::GENITIVE, 'книги'],
            'земля (dative)' => ['земля', GrammaticalCase::DATIVE, 'землі'],
            'ніч (instrumental)' => ['ніч', GrammaticalCase::INSTRUMENTAL, 'ніччю'],
            'любов (vocative)' => ['любов', GrammaticalCase::VOCATIVE, 'любове'],

            // Neuter
            'вікно (instrumental)' => ['вікно', GrammaticalCase::INSTRUMENTAL, 'вікном'],
            'море (locative)' => ['море', GrammaticalCase::LOCATIVE, 'морі'],
            'життя (genitive)' => ['життя', GrammaticalCase::GENITIVE, 'життя'],

            // Masculine Exceptions
            'батько (genitive)' => ['батько', GrammaticalCase::GENITIVE, 'батька'],
            'тато (dative)' => ['тато', GrammaticalCase::DATIVE, 'тату'],
            'дідо (instrumental)' => ['дідо', GrammaticalCase::INSTRUMENTAL, 'дідом'],
            'Петро (vocative)' => ['Петро', GrammaticalCase::VOCATIVE, 'Петре'],
            'Микола (genitive)' => ['Микола', GrammaticalCase::GENITIVE, 'Миколи'],

            // Feminine Exceptions
            'мати (genitive)' => ['мати', GrammaticalCase::GENITIVE, 'матері'],
            'осінь (dative)' => ['осінь', GrammaticalCase::DATIVE, 'осені'],
            'сіль (instrumental)' => ['сіль', GrammaticalCase::INSTRUMENTAL, 'сіллю'],

            // Neuter Exceptions
            'кошеня (genitive)' => ['кошеня', GrammaticalCase::GENITIVE, 'кошеняти'],
            'ягня (dative)' => ['ягня', GrammaticalCase::DATIVE, 'ягняті'],
            'ім\'я (instrumental)' => ['ім\'я', GrammaticalCase::INSTRUMENTAL, 'іменем'],
        ];
    }
} 