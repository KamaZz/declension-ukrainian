<?php

namespace UkrainianDeclension\Services;

use UkrainianDeclension\Contracts\DeclensionerContract;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Contracts\DeclensionGroupIdentifierContract;
use UkrainianDeclension\Enums\Declension;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\Rules\FirstDeclensionRule;
use UkrainianDeclension\Rules\SecondDeclensionRule;
use UkrainianDeclension\Rules\ThirdDeclensionRule;
use UkrainianDeclension\Rules\FourthDeclensionRule;
use UkrainianDeclension\Exceptions\UnsupportedWordException;
use UkrainianDeclension\Utils\WordHelper;

class Declensioner implements DeclensionerContract
{
    protected DeclensionGroupIdentifierContract $identifier;
    protected ?PhraseDeclensioner $phraseDeclensioner = null;
    protected array $rules = [];

    public function __construct(DeclensionGroupIdentifierContract $identifier)
    {
        $this->identifier = $identifier;
    }

    public function setPhraseDeclensioner(PhraseDeclensioner $phraseDeclensioner): void
    {
        $this->phraseDeclensioner = $phraseDeclensioner;
    }

    /**
     * @inheritDoc
     */
    public function decline(string $word, GrammaticalCase $case, Number $number, ?Gender $gender = null): string
    {
        if ($this->phraseDeclensioner !== null && str_contains($word, ' ')) {
            return $this->phraseDeclensioner->decline($word, $case, $number, $gender);
        }

        if ($gender === null) {
            $gender = $this->guessGender($word);
        }

        $declension_group = $this->identifier->identify($word, $gender);

        if ($declension_group === Declension::INDECLINABLE) {
            return $word;
        }

        $this->rules = [
            Declension::FIRST->value => new FirstDeclensionRule(),
            Declension::SECOND->value => new SecondDeclensionRule($gender),
            Declension::THIRD->value => new ThirdDeclensionRule(),
            Declension::FOURTH->value => new FourthDeclensionRule(),
        ];
        
        if (!isset($this->rules[$declension_group->value])) {
            throw new UnsupportedWordException("No declension rule found for group [{$declension_group->value}].");
        }
        
        return $this->rules[$declension_group->value]->decline($word, $case, $number);
    }

    private function guessGender(string $word): Gender
    {
        $word_lower = mb_strtolower($word);

        $masculine_exceptions = ['тато', 'батько', 'дідо', 'петро', 'микола'];
        if (in_array($word_lower, $masculine_exceptions, true)) {
            return Gender::MASCULINE;
        }

        $feminine_exceptions = ['мати', 'ніч', 'осінь', 'сіль', 'любов', 'тінь'];
        if (in_array($word_lower, $feminine_exceptions, true)) {
            return Gender::FEMININE;
        }

        $neuter_exceptions = ['життя', 'щастя', 'ягня', 'кошеня', 'ім\'я'];
        if (in_array($word_lower, $neuter_exceptions, true)) {
            return Gender::NEUTER;
        }

        // General rules based on word endings.
        if (WordHelper::endsWith($word_lower, ['а', 'я'])) {
            return Gender::FEMININE;
        }

        if (WordHelper::endsWith($word_lower, ['о', 'е'])) {
            return Gender::NEUTER;
        }

        return Gender::MASCULINE;
    }
}