<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\UkrainianDeclension;

class NameDeclensionTest extends TestCase
{
    #[DataProvider('masculineNames')]
    public function testMasculineNames(string $name, GrammaticalCase $case, string $expected): void
    {
        $result = UkrainianDeclension::decline($name, $case, Number::SINGULAR);
        $this->assertEquals($expected, $result);
    }

    public static function masculineNames(): \Generator
    {
        $names = [
            'Пінчук Остап Аркадійович' => [
                'genitive' => 'Пінчука Остапа Аркадійовича',
                'dative' => 'Пінчуку Остапу Аркадійовичу',
                'accusative' => 'Пінчука Остапа Аркадійовича',
                'instrumental' => 'Пінчуком Остапом Аркадійовичем',
                'locative' => 'Пінчукові Остапові Аркадійовичу',
                'vocative' => 'Пінчуку Остапе Аркадійовичу',
            ],
            'Тарасенко Леонід Васильович' => [
                'genitive' => 'Тарасенка Леоніда Васильовича',
                'dative' => 'Тарасенку Леоніду Васильовичу',
                'accusative' => 'Тарасенка Леоніда Васильовича',
                'instrumental' => 'Тарасенком Леонідом Васильовичем',
                'locative' => 'Тарасенку Леонідові Васильовичу',
                'vocative' => 'Тарасенко Леоніде Васильовичу', // Fixed: surname -енко remains unchanged in vocative (consistent with other -енко surnames)
            ],
            'Руда Сергій Казимирович' => [
                'genitive' => 'Руди Сергія Казимировича',
                'dative' => 'Руді Сергію Казимировичу',
                'accusative' => 'Руду Сергія Казимировича',
                'instrumental' => 'Рудою Сергієм Казимировичем',
                'locative' => 'Руді Сергієві Казимировичу',
                'vocative' => 'Рудо Сергію Казимировичу',
            ],
            'Горобець Віктор Ростиславович' => [
                'genitive' => 'Горобця Віктора Ростиславовича',
                'dative' => 'Горобцю Віктору Ростиславовичу',
                'accusative' => 'Горобця Віктора Ростиславовича',
                'instrumental' => 'Горобцем Віктором Ростиславовичем',
                'locative' => 'Горобцеві Вікторові Ростиславовичу',
                'vocative' => 'Горобцю Вікторе Ростиславовичу',
            ],
            'Деркач Михайло Олександрович' => [
                'genitive' => 'Деркача Михайла Олександровича',
                'dative' => 'Деркачу Михайлу Олександровичу',
                'accusative' => 'Деркача Михайла Олександровича',
                'instrumental' => 'Деркачем Михайлом Олександровичем',
                'locative' => 'Деркачу Михайлові Олександровичу',
                'vocative' => 'Деркачу Михайле Олександровичу',
            ],
            'Пасічник Назар Федорович' => [
                'genitive' => 'Пасічника Назара Федоровича',
                'dative' => 'Пасічнику Назару Федоровичу',
                'accusative' => 'Пасічника Назара Федоровича',
                'instrumental' => 'Пасічником Назаром Федоровичем',
                'locative' => 'Пасічнику Назарові Федоровичу',
                'vocative' => 'Пасічнику Назаре Федоровичу',
            ],
            'Дубина Сава Орестович' => [
                'genitive' => 'Дубини Сави Орестовича',
                'dative' => 'Дубині Саві Орестовичу',
                'accusative' => 'Дубину Саву Орестовича',
                'instrumental' => 'Дубиною Савою Орестовичем',
                'locative' => 'Дубині Савові Орестовичу',
                'vocative' => 'Дубино Саво Орестовичу',
            ],
            'Тимошенко Вадим Владиславович' => [
                'genitive' => 'Тимошенка Вадима Владиславовича',
                'dative' => 'Тимошенку Вадиму Владиславовичу',
                'accusative' => 'Тимошенка Вадима Владиславовича',
                'instrumental' => 'Тимошенком Вадимом Владиславовичем',
                'locative' => 'Тимошенку Вадимові Владиславовичу',
                'vocative' => 'Тимошенко Вадиме Владиславовичу', // Fixed: surname -енко remains unchanged in vocative
            ],
            'Васильченко Руслан Пилипович' => [
                'genitive' => 'Васильченка Руслана Пилиповича',
                'dative' => 'Васильченку Руслану Пилиповичу', // Fixed: consistent spelling
                'accusative' => 'Васильченка Руслана Пилиповича', // Fixed: consistent spelling
                'instrumental' => 'Васильченком Русланом Пилиповичем', // Fixed: consistent spelling
                'locative' => 'Васильченку Русланові Пилиповичу', // Fixed: consistent spelling
                'vocative' => 'Васильченко Руслане Пилиповичу', // Fixed: surname -енко remains unchanged in vocative
            ],
            'Буряк Віталій Вадимович' => [
                'genitive' => 'Буряка Віталія Вадимовича',
                'dative' => 'Буряку Віталію Вадимовичу',
                'accusative' => 'Буряка Віталія Вадимовича',
                'instrumental' => 'Буряком Віталієм Вадимовичем',
                'locative' => 'Бурякові Віталієві Вадимовичу',
                'vocative' => 'Буряку Віталію Вадимовичу', // Fixed: surname should be declined in vocative
            ],
        ];

        foreach ($names as $name => $cases) {
            yield "{$name} (nominative)" => [$name, GrammaticalCase::NOMINATIVE, $name];
            foreach ($cases as $case => $expected) {
                yield "{$name} ({$case})" => [$name, GrammaticalCase::from($case), $expected];
            }
        }
    }

    #[DataProvider('feminineNames')]
    public function testFeminineNames(string $name, GrammaticalCase $case, string $expected): void
    {
        $result = UkrainianDeclension::decline($name, $case, Number::SINGULAR);
        $this->assertEquals($expected, $result);
    }

    public static function feminineNames(): \Generator
    {
        $names = [
            'Яценко Ліна Георгіївна' => [
                'genitive' => 'Яценко Ліни Георгіївни',
                'dative' => 'Яценко Ліні Георгіївні',
                'accusative' => 'Яценко Ліну Георгіївну',
                'instrumental' => 'Яценко Ліною Георгіївною',
                'locative' => 'Яценко Ліні Георгіївні',
                'vocative' => 'Яценко Ліно Георгіївно',
            ],
            'Перепелиця Владислава Орестівна' => [
                'genitive' => 'Перепелиці Владислави Орестівни',
                'dative' => 'Перепелиці Владиславі Орестівні',
                'accusative' => 'Перепелицю Владиславу Орестівну',
                'instrumental' => 'Перепелицею Владиславою Орестівною',
                'locative' => 'Перепелиці Владиславі Орестівні',
                'vocative' => 'Перепелице Владиславо Орестівно',
            ],
            'Лазаренко Єлізавета Василівна' => [
                'genitive' => 'Лазаренко Єлізавети Василівни',
                'dative' => 'Лазаренко Єлізаветі Василівні',
                'accusative' => 'Лазаренко Єлізавету Василівну',
                'instrumental' => 'Лазаренко Єлізаветою Василівною',
                'locative' => 'Лазаренко Єлізаветі Василівні',
                'vocative' => 'Лазаренко Єлізавето Василівно',
            ],
            'Голуб Оксана Генадіївна' => [
                'genitive' => 'Голуб Оксани Генадіївни',
                'dative' => 'Голуб Оксані Генадіївні',
                'accusative' => 'Голуб Оксану Генадіївну',
                'instrumental' => 'Голуб Оксаною Генадіївною',
                'locative' => 'Голуб Оксані Генадіївні',
                'vocative' => 'Голуб Оксано Генадіївно',
            ],
            'Степаненко Надія Данилівна' => [
                'genitive' => 'Степаненко Надії Данилівни',
                'dative' => 'Степаненко Надії Данилівні',
                'accusative' => 'Степаненко Надію Данилівну',
                'instrumental' => 'Степаненко Надією Данилівною',
                'locative' => 'Степаненко Надії Данилівні',
                'vocative' => 'Степаненко Надіє Данилівно',
            ],
            'Даниленко Стефанія Олександрівна' => [
                'genitive' => 'Даниленко Стефанії Олександрівни',
                'dative' => 'Даниленко Стефанії Олександрівні',
                'accusative' => 'Даниленко Стефанію Олександрівну',
                'instrumental' => 'Даниленко Стефанією Олександрівною',
                'locative' => 'Даниленко Стефанії Олександрівні',
                'vocative' => 'Даниленко Стефаніє Олександрівно',
            ],
            'Шаповалова Євдокія Тимофіївна' => [
                'genitive' => 'Шаповалової Євдокії Тимофіївни',
                'dative' => 'Шаповаловій Євдокії Тимофіївні',
                'accusative' => 'Шаповалову Євдокію Тимофіївну',
                'instrumental' => 'Шаповаловою Євдокією Тимофіївною',
                'locative' => 'Шаповаловій Євдокії Тимофіївні',
                'vocative' => 'Шаповалова Євдокіє Тимофіївно',
            ],
            'Боровик Анжела Едуардівна' => [
                'genitive' => 'Боровик Анжели Едуардівни',
                'dative' => 'Боровик Анжелі Едуардівні',
                'accusative' => 'Боровик Анжелу Едуардівну',
                'instrumental' => 'Боровик Анжелою Едуардівною',
                'locative' => 'Боровик Анжелі Едуардівні',
                'vocative' => 'Боровик Анжело Едуардівно',
            ],
            'Присяжнюк Евгенія Денисівна' => [
                'genitive' => 'Присяжнюк Евгенії Денисівни',
                'dative' => 'Присяжнюк Евгенії Денисівні',
                'accusative' => 'Присяжнюк Евгенію Денисівну',
                'instrumental' => 'Присяжнюк Евгенією Денисівною',
                'locative' => 'Присяжнюк Евгенії Денисівні',
                'vocative' => 'Присяжнюк Евгеніє Денисівно',
            ],
            'Михайлова Тетяна Мирославівна' => [
                'genitive' => 'Михайлової Тетяни Мирославівни',
                'dative' => 'Михайловій Тетяні Мирославівні',
                'accusative' => 'Михайлову Тетяну Мирославівну',
                'instrumental' => 'Михайловою Тетяною Мирославівною',
                'locative' => 'Михайловій Тетяні Мирославівні',
                'vocative' => 'Михайлова Тетяно Мирославівно',
            ],
        ];

        foreach ($names as $name => $cases) {
            yield "{$name} (nominative)" => [$name, GrammaticalCase::NOMINATIVE, $name];
            foreach ($cases as $case => $expected) {
                yield "{$name} ({$case})" => [$name, GrammaticalCase::from($case), $expected];
            }
        }
    }
} 