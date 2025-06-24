<?php

namespace UkrainianDeclension\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string decline(string $phrase, \UkrainianDeclension\Enums\GrammaticalCase $case, \UkrainianDeclension\Enums\Number $number, ?\UkrainianDeclension\Enums\Gender $gender = null)
 *
 * @see \UkrainianDeclension\Services\Declensioner
 */
class Declensioner extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'declensioner';
    }
} 