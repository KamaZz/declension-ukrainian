# Ukrainian Declension Library

A simple PHP library for declining Ukrainian nouns. This library provides a straightforward way to get the correct grammatical form of a noun for any of the seven Ukrainian cases, in both singular and plural forms.

## Installation

Install the library via Composer:

```bash
composer require ukrainian-declension/core
```

## Usage in Laravel

This library includes a Service Provider for easy integration with Laravel applications.

1.  The provider will be auto-discovered by Laravel.
2.  You can now inject the `DeclensionerContract` anywhere in your application.

## Basic Usage

Here is a simple example of how to decline a word:

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

The library can automatically guess the gender of a noun based on its ending and a set of grammatical exceptions. This means you can omit the `Gender` parameter when calling the `decline` method.

```php
// The library will correctly guess that "книга" is feminine.
$declined = $this->declensioner->decline('книга', GrammaticalCase::GENITIVE, Number::SINGULAR);
```

However, you can still provide the gender for ambiguous words or to ensure accuracy.

```php
// Explicitly providing the gender.
$declined = $this->declensioner->decline('сіль', GrammaticalCase::INSTRUMENTAL, Number::SINGULAR, Gender::FEMININE);
```

### Available Enums

The library provides several enums to ensure type-safety and clarity:

#### `GrammaticalCase`

-   `NOMINATIVE`
-   `GENITIVE`
-   `DATIVE`
-   `ACCUSATIVE`
-   `INSTRUMENTAL`
-   `LOCATIVE`
-   `VOCATIVE`

#### `Gender`

-   `MASCULINE`
-   `FEMININE`
-   `NEUTER`

#### `Number`

-   `SINGULAR`
-   `PLURAL`

## Testing

To run the tests, execute the following command from the project root:

```bash
./vendor/bin/phpunit
``` 