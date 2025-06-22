<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Gender;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\Services\Declensioner;
use UkrainianDeclension\Services\DeclensionGroupIdentifier;

class NameDeclensionTest extends TestCase
{
    private Declensioner $declensioner;

    protected function setUp(): void
    {
        parent::setUp();
        $identifier = new DeclensionGroupIdentifier();
        $this->declensioner = new Declensioner($identifier);
    }

    #[DataProvider('masculineNames')]
    public function testMasculineNames(array $nameParts, GrammaticalCase $case, array $expectedParts): void
    {
        [$lastName, $firstName, $patronymic] = $nameParts;
        [$expectedLastName, $expectedFirstName, $expectedPatronymic] = $expectedParts;

        $resultFirstName = $this->declensioner->decline($firstName, $case, Number::SINGULAR, Gender::MASCULINE);
        $resultPatronymic = $this->declensioner->decline($patronymic, $case, Number::SINGULAR, Gender::MASCULINE);
        $resultLastName = $this->declensioner->decline($lastName, $case, Number::SINGULAR, Gender::MASCULINE);

        $this->assertEquals($expectedFirstName, $resultFirstName, "First name failed: {$firstName}");
        $this->assertEquals($expectedPatronymic, $resultPatronymic, "Patronymic failed: {$patronymic}");
        $this->assertEquals($expectedLastName, $resultLastName, "Last name failed: {$lastName}");
    }

    public static function masculineNames(): \Generator
    {
        $names = [
            'Остап Аркадійович Пінчук' => [
                'nominative' => ['Пінчук', 'Остап', 'Аркадійович'],
                'genitive' => ['Пінчука', 'Остапа', 'Аркадійовича'],
                'dative' => ['Пінчуку', 'Остапу', 'Аркадійовичу'],
                'accusative' => ['Пінчука', 'Остапа', 'Аркадійовича'],
                'instrumental' => ['Пінчуком', 'Остапом', 'Аркадійовичем'],
                'locative' => ['Пінчукові', 'Остапові', 'Аркадійовичу'],
                'vocative' => ['Пінчуку', 'Остапе', 'Аркадійовичу'],
            ],
            'Леонід Васильович Тарасенко' => [
                'nominative' => ['Тарасенко', 'Леонід', 'Васильович'],
                'genitive' => ['Тарасенка', 'Леоніда', 'Васильовича'],
                'dative' => ['Тарасенку', 'Леоніду', 'Васильовичу'],
                'accusative' => ['Тарасенка', 'Леоніда', 'Васильовича'],
                'instrumental' => ['Тарасенком', 'Леонідом', 'Васильовичем'],
                'locative' => ['Тарасенку', 'Леонідові', 'Васильовичу'],
                'vocative' => ['Тарасенку', 'Леоніде', 'Васильовичу'],
            ],
            'Сергій Казимирович Руда' => [
                'nominative' => ['Руда', 'Сергій', 'Казимирович'],
                'genitive' => ['Руди', 'Сергія', 'Казимировича'],
                'dative' => ['Руді', 'Сергію', 'Казимировичу'],
                'accusative' => ['Руду', 'Сергія', 'Казимировича'],
                'instrumental' => ['Рудою', 'Сергієм', 'Казимировичем'],
                'locative' => ['Руді', 'Сергієві', 'Казимировичу'],
                'vocative' => ['Рудо', 'Сергію', 'Казимировичу'],
            ],
            'Віктор Ростиславович Горобець' => [
                'nominative' => ['Горобець', 'Віктор', 'Ростиславович'],
                'genitive' => ['Горобця', 'Віктора', 'Ростиславовича'],
                'dative' => ['Горобцю', 'Віктору', 'Ростиславовичу'],
                'accusative' => ['Горобця', 'Віктора', 'Ростиславовича'],
                'instrumental' => ['Горобцем', 'Віктором', 'Ростиславовичем'],
                'locative' => ['Горобцеві', 'Вікторові', 'Ростиславовичу'],
                'vocative' => ['Горобцю', 'Вікторе', 'Ростиславовичу'],
            ],
            'Михайло Олександрович Деркач' => [
                'nominative' => ['Деркач', 'Михайло', 'Олександрович'],
                'genitive' => ['Деркача', 'Михайла', 'Олександровича'],
                'dative' => ['Деркачу', 'Михайлу', 'Олександровичу'],
                'accusative' => ['Деркача', 'Михайла', 'Олександровича'],
                'instrumental' => ['Деркачем', 'Михайлом', 'Олександровичем'],
                'locative' => ['Деркачу', 'Михайлові', 'Олександровичу'],
                'vocative' => ['Деркачу', 'Михайле', 'Олександровичу'],
            ],
            'Назар Федорович Пасічник' => [
                'nominative' => ['Пасічник', 'Назар', 'Федорович'],
                'genitive' => ['Пасічника', 'Назара', 'Федоровича'],
                'dative' => ['Пасічнику', 'Назару', 'Федоровичу'],
                'accusative' => ['Пасічника', 'Назара', 'Федоровича'],
                'instrumental' => ['Пасічником', 'Назаром', 'Федоровичем'],
                'locative' => ['Пасічнику', 'Назарові', 'Федоровичу'],
                'vocative' => ['Пасічнику', 'Назаре', 'Федоровичу'],
            ],
            'Сава Орестович Дубина' => [
                'nominative' => ['Дубина', 'Сава', 'Орестович'],
                'genitive' => ['Дубини', 'Сави', 'Орестовича'],
                'dative' => ['Дубині', 'Саві', 'Орестовичу'],
                'accusative' => ['Дубину', 'Саву', 'Орестовича'],
                'instrumental' => ['Дубиною', 'Савою', 'Орестовичем'],
                'locative' => ['Дубині', 'Савові', 'Орестовичу'],
                'vocative' => ['Дубино', 'Саво', 'Орестовичу'],
            ],
            'Вадим Владиславович Тимошенко' => [
                'nominative' => ['Тимошенко', 'Вадим', 'Владиславович'],
                'genitive' => ['Тимошенка', 'Вадима', 'Владиславовича'],
                'dative' => ['Тимошенку', 'Вадиму', 'Владиславовичу'],
                'accusative' => ['Тимошенка', 'Вадима', 'Владиславовича'],
                'instrumental' => ['Тимошенком', 'Вадимом', 'Владиславовичем'],
                'locative' => ['Тимошенку', 'Вадимові', 'Владиславовичу'],
                'vocative' => ['Тимошенку', 'Вадиме', 'Владиславовичу'],
            ],
            'Руслан Пилипович Васильченко' => [
                'nominative' => ['Васильченко', 'Руслан', 'Пилипович'],
                'genitive' => ['Васильченка', 'Руслана', 'Пилиповича'],
                'dative' => ['Васильченку', 'Руслану', 'Пилиповичу'],
                'accusative' => ['Васильченка', 'Руслана', 'Пилиповича'],
                'instrumental' => ['Васильченком', 'Русланом', 'Пилиповичем'],
                'locative' => ['Васильченку', 'Русланові', 'Пилиповичу'],
                'vocative' => ['Васильченку', 'Руслане', 'Пилиповичу'],
            ],
            'Віталій Вадимович Буряк' => [
                'nominative' => ['Буряк', 'Віталій', 'Вадимович'],
                'genitive' => ['Буряка', 'Віталія', 'Вадимовича'],
                'dative' => ['Буряку', 'Віталію', 'Вадимовичу'],
                'accusative' => ['Буряка', 'Віталія', 'Вадимовича'],
                'instrumental' => ['Буряком', 'Віталієм', 'Вадимовичем'],
                'locative' => ['Бурякові', 'Віталієві', 'Вадимовичу'],
                'vocative' => ['Буряку', 'Віталію', 'Вадимовичу'],
            ],
        ];

        foreach ($names as $name => $cases) {
            yield "{$name} (nominative)" => [$cases['nominative'], GrammaticalCase::NOMINATIVE, $cases['nominative']];
            yield "{$name} (genitive)" => [$cases['nominative'], GrammaticalCase::GENITIVE, $cases['genitive']];
            yield "{$name} (dative)" => [$cases['nominative'], GrammaticalCase::DATIVE, $cases['dative']];
            yield "{$name} (accusative)" => [$cases['nominative'], GrammaticalCase::ACCUSATIVE, $cases['accusative']];
            yield "{$name} (instrumental)" => [$cases['nominative'], GrammaticalCase::INSTRUMENTAL, $cases['instrumental']];
            yield "{$name} (locative)" => [$cases['nominative'], GrammaticalCase::LOCATIVE, $cases['locative']];
            yield "{$name} (vocative)" => [$cases['nominative'], GrammaticalCase::VOCATIVE, $cases['vocative']];
        }
    }

    #[DataProvider('feminineNames')]
    public function testFeminineNames(array $nameParts, GrammaticalCase $case, array $expectedParts): void
    {
        [$lastName, $firstName, $patronymic] = $nameParts;
        [$expectedLastName, $expectedFirstName, $expectedPatronymic] = $expectedParts;

        $resultFirstName = $this->declensioner->decline($firstName, $case, Number::SINGULAR, Gender::FEMININE);
        $resultPatronymic = $this->declensioner->decline($patronymic, $case, Number::SINGULAR, Gender::FEMININE);
        $resultLastName = $this->declensioner->decline($lastName, $case, Number::SINGULAR, Gender::FEMININE);

        $this->assertEquals($expectedFirstName, $resultFirstName, "First name failed: {$firstName}");
        $this->assertEquals($expectedPatronymic, $resultPatronymic, "Patronymic failed: {$patronymic}");
        $this->assertEquals($expectedLastName, $resultLastName, "Last name failed: {$lastName}");
    }

    public static function feminineNames(): \Generator
    {
        $names = [
            'Ліна Георгіївна Яценко' => [
                'nominative' => ['Яценко', 'Ліна', 'Георгіївна'],
                'genitive' => ['Яценко', 'Ліни', 'Георгіївни'],
                'dative' => ['Яценко', 'Ліні', 'Георгіївні'],
                'accusative' => ['Яценко', 'Ліну', 'Георгіївну'],
                'instrumental' => ['Яценко', 'Ліною', 'Георгіївною'],
                'locative' => ['Яценко', 'Ліні', 'Георгіївні'],
                'vocative' => ['Яценко', 'Ліно', 'Георгіївно'],
            ],
            'Владислава Орестівна Перепелиця' => [
                'nominative' => ['Перепелиця', 'Владислава', 'Орестівна'],
                'genitive' => ['Перепелиці', 'Владислави', 'Орестівни'],
                'dative' => ['Перепелиці', 'Владиславі', 'Орестівні'],
                'accusative' => ['Перепелицю', 'Владиславу', 'Орестівну'],
                'instrumental' => ['Перепелицею', 'Владиславою', 'Орестівною'],
                'locative' => ['Перепелиці', 'Владиславі', 'Орестівні'],
                'vocative' => ['Перепелице', 'Владиславо', 'Орестівно'],
            ],
            'Єлізавета Василівна Лазаренко' => [
                'nominative' => ['Лазаренко', 'Єлізавета', 'Василівна'],
                'genitive' => ['Лазаренко', 'Єлізавети', 'Василівни'],
                'dative' => ['Лазаренко', 'Єлізаветі', 'Василівні'],
                'accusative' => ['Лазаренко', 'Єлізавету', 'Василівну'],
                'instrumental' => ['Лазаренко', 'Єлізаветою', 'Василівною'],
                'locative' => ['Лазаренко', 'Єлізаветі', 'Василівні'],
                'vocative' => ['Лазаренко', 'Єлізавето', 'Василівно'],
            ],
            'Оксана Генадіївна Голуб' => [
                'nominative' => ['Голуб', 'Оксана', 'Генадіївна'],
                'genitive' => ['Голуб', 'Оксани', 'Генадіївни'],
                'dative' => ['Голуб', 'Оксані', 'Генадіївні'],
                'accusative' => ['Голуб', 'Оксану', 'Генадіївну'],
                'instrumental' => ['Голуб', 'Оксаною', 'Генадіївною'],
                'locative' => ['Голуб', 'Оксані', 'Генадіївні'],
                'vocative' => ['Голуб', 'Оксано', 'Генадіївно'],
            ],
            'Надія Данилівна Степаненко' => [
                'nominative' => ['Степаненко', 'Надія', 'Данилівна'],
                'genitive' => ['Степаненко', 'Надії', 'Данилівни'],
                'dative' => ['Степаненко', 'Надії', 'Данилівні'],
                'accusative' => ['Степаненко', 'Надію', 'Данилівну'],
                'instrumental' => ['Степаненко', 'Надією', 'Данилівною'],
                'locative' => ['Степаненко', 'Надії', 'Данилівні'],
                'vocative' => ['Степаненко', 'Надіє', 'Данилівно'],
            ],
            'Стефанія Олександрівна Даниленко' => [
                'nominative' => ['Даниленко', 'Стефанія', 'Олександрівна'],
                'genitive' => ['Даниленко', 'Стефанії', 'Олександрівни'],
                'dative' => ['Даниленко', 'Стефанії', 'Олександрівні'],
                'accusative' => ['Даниленко', 'Стефанію', 'Олександрівну'],
                'instrumental' => ['Даниленко', 'Стефанією', 'Олександрівною'],
                'locative' => ['Даниленко', 'Стефанії', 'Олександрівні'],
                'vocative' => ['Даниленко', 'Стефаніє', 'Олександрівно'],
            ],
            'Євдокія Тимофіївна Шаповалова' => [
                'nominative' => ['Шаповалова', 'Євдокія', 'Тимофіївна'],
                'genitive' => ['Шаповалової', 'Євдокії', 'Тимофіївни'],
                'dative' => ['Шаповаловій', 'Євдокії', 'Тимофіївні'],
                'accusative' => ['Шаповалову', 'Євдокію', 'Тимофіївну'],
                'instrumental' => ['Шаповаловою', 'Євдокією', 'Тимофіївною'],
                'locative' => ['Шаповаловій', 'Євдокії', 'Тимофіївні'],
                'vocative' => ['Шаповалова', 'Євдокіє', 'Тимофіївно'],
            ],
            'Анжела Едуардівна Боровик' => [
                'nominative' => ['Боровик', 'Анжела', 'Едуардівна'],
                'genitive' => ['Боровик', 'Анжели', 'Едуардівни'],
                'dative' => ['Боровик', 'Анжелі', 'Едуардівні'],
                'accusative' => ['Боровик', 'Анжелу', 'Едуардівну'],
                'instrumental' => ['Боровик', 'Анжелою', 'Едуардівною'],
                'locative' => ['Боровик', 'Анжелі', 'Едуардівні'],
                'vocative' => ['Боровик', 'Анжело', 'Едуардівно'],
            ],
            'Евгенія Денисівна Присяжнюк' => [
                'nominative' => ['Присяжнюк', 'Евгенія', 'Денисівна'],
                'genitive' => ['Присяжнюк', 'Евгенії', 'Денисівни'],
                'dative' => ['Присяжнюк', 'Евгенії', 'Денисівні'],
                'accusative' => ['Присяжнюк', 'Евгенію', 'Денисівну'],
                'instrumental' => ['Присяжнюк', 'Евгенією', 'Денисівною'],
                'locative' => ['Присяжнюк', 'Евгенії', 'Денисівні'],
                'vocative' => ['Присяжнюк', 'Евгеніє', 'Денисівно'],
            ],
            'Тетяна Мирославівна Михайлова' => [
                'nominative' => ['Михайлова', 'Тетяна', 'Мирославівна'],
                'genitive' => ['Михайлової', 'Тетяни', 'Мирославівни'],
                'dative' => ['Михайловій', 'Тетяні', 'Мирославівні'],
                'accusative' => ['Михайлову', 'Тетяну', 'Мирославівну'],
                'instrumental' => ['Михайловою', 'Тетяною', 'Мирославівною'],
                'locative' => ['Михайловій', 'Тетяні', 'Мирославівні'],
                'vocative' => ['Михайлова', 'Тетяно', 'Мирославівно'],
            ],
        ];

        foreach ($names as $name => $cases) {
            yield "{$name} (nominative)" => [$cases['nominative'], GrammaticalCase::NOMINATIVE, $cases['nominative']];
            yield "{$name} (genitive)" => [$cases['nominative'], GrammaticalCase::GENITIVE, $cases['genitive']];
            yield "{$name} (dative)" => [$cases['nominative'], GrammaticalCase::DATIVE, $cases['dative']];
            yield "{$name} (accusative)" => [$cases['nominative'], GrammaticalCase::ACCUSATIVE, $cases['accusative']];
            yield "{$name} (instrumental)" => [$cases['nominative'], GrammaticalCase::INSTRUMENTAL, $cases['instrumental']];
            yield "{$name} (locative)" => [$cases['nominative'], GrammaticalCase::LOCATIVE, $cases['locative']];
            yield "{$name} (vocative)" => [$cases['nominative'], GrammaticalCase::VOCATIVE, $cases['vocative']];
        }
    }
} 