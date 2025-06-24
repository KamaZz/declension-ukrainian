# Ukrainian Declension Library

A simple PHP library for declining Ukrainian nouns and phrases. This library provides a straightforward way to get the correct grammatical form of a noun for any of the seven Ukrainian cases, in both singular and plural forms.

## Installation

Install the library via Composer:

```bash
composer require ukrainian-declension/core
```

## Basic Usage

The easiest way to use the library is by calling the static `decline` method.

```php
use UkrainianDeclension\UkrainianDeclension;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Number;

// Decline a single word
$declinedWord = UkrainianDeclension::decline('книга', GrammaticalCase::GENITIVE, Number::SINGULAR);
echo $declinedWord; // Outputs: книги

// Decline a full name
$declinedName = UkrainianDeclension::decline('Петренко Олексій Іванович', GrammaticalCase::DATIVE, Number::SINGULAR);
echo $declinedName; // Outputs: Петренку Олексію Івановичу
```

## Usage in Laravel

This library includes a Service Provider and Facade for easy integration with Laravel applications.

1.  The provider and facade will be auto-discovered by Laravel.
2.  You can use the `Declensioner` facade directly or inject the `DeclensionerContract` anywhere in your application.

### Using the Facade
```php
use UkrainianDeclension\Facades\Declensioner;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Number;

// Decline a phrase
$declinedPhrase = Declensioner::decline('молодший лейтенант', GrammaticalCase::INSTRUMENTAL, Number::SINGULAR);
echo $declinedPhrase; // Outputs: молодшим лейтенантом
```

### Using Dependency Injection

```php
use UkrainianDeclension\Contracts\DeclensionerContract;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;

class MyController
{
    private $declensioner;

    public function __construct(DeclensionerContract $declensioner)
    {
        $this->declensioner = $declensioner;
    }

    public function showDeclinedWord()
    {
        $word = 'книга';

        // Decline to Genitive Singular: "книги"
        $declined_singular = $this->declensioner->decline($word, GrammaticalCase::GENITIVE, Number::SINGULAR);

        // Decline to Nominative Plural: "книги"
        $declined_plural = $this->declensioner->decline($word, GrammaticalCase::NOMINATIVE, Number::PLURAL);

        echo $declined_singular; // Outputs: книги
        echo $declined_plural; // Outputs: книги
    }
}
```

### Gender Guessing

The library can automatically guess the gender of a noun or a name within a phrase. For names, it often identifies gender by the patronymic (e.g., words ending in -ович for masculine, -івна for feminine). For single words, it uses endings and a list of exceptions.

This means you can often omit the `Gender` parameter.

```php
// The library will correctly guess that "книга" is feminine.
$declined = UkrainianDeclension::decline('книга', GrammaticalCase::GENITIVE, Number::SINGULAR);

// The library will guess the gender is masculine from the patronymic 'Іванович'.
$declinedName = UkrainianDeclension::decline('Петренко Олексій Іванович', GrammaticalCase::DATIVE, Number::SINGULAR);
```

However, you can still provide the gender for ambiguous words or to ensure accuracy.