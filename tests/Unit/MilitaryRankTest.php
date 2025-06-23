<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use UkrainianDeclension\Enums\GrammaticalCase;
use UkrainianDeclension\Enums\Number;
use UkrainianDeclension\UkrainianDeclension;

class MilitaryRankTest extends TestCase
{
    #[DataProvider('militaryRankNames')]
    public function testMilitaryRankNames(string $name, GrammaticalCase $case, string $expected): void
    {
        $result = UkrainianDeclension::decline($name, $case, Number::SINGULAR);
        $this->assertEquals($expected, $result);
    }

    public static function militaryRankNames(): \Generator
    {
        $names = [
            'підполковник СУЧКОВ Віталій Олександрович' => [
                'genitive' => 'підполковника СУЧКОВА Віталія Олександровича',
                'dative' => 'підполковнику СУЧКОВУ Віталію Олександровичу',
                'accusative' => 'підполковника СУЧКОВА Віталія Олександровича',
                'instrumental' => 'підполковником СУЧКОВИМ Віталієм Олександровичем',
                'locative' => 'підполковнику СУЧКОВУ Віталієві Олександровичу',
                'vocative' => 'підполковнику СУЧКОВУ Віталію Олександровичу',
            ],
            'капітан ПЕТРЕНКО Олександр Іванович' => [
                'genitive' => 'капітана ПЕТРЕНКА Олександра Івановича',
                'dative' => 'капітану ПЕТРЕНКУ Олександру Івановичу',
                'accusative' => 'капітана ПЕТРЕНКА Олександра Івановича',
                'instrumental' => 'капітаном ПЕТРЕНКОМ Олександром Івановичем',
                'locative' => 'капітану ПЕТРЕНКУ Олександрові Івановичу',
                'vocative' => 'капітане ПЕТРЕНКО Олександре Івановичу',
            ],
            'майор КОВАЛЕНКО Сергій Петрович' => [
                'genitive' => 'майора КОВАЛЕНКА Сергія Петровича',
                'dative' => 'майору КОВАЛЕНКУ Сергію Петровичу',
                'accusative' => 'майора КОВАЛЕНКА Сергія Петровича',
                'instrumental' => 'майором КОВАЛЕНКОМ Сергієм Петровичем',
                'locative' => 'майору КОВАЛЕНКУ Сергієві Петровичу',
                'vocative' => 'майоре КОВАЛЕНКО Сергію Петровичу',
            ],
            'старший лейтенант ДЖУРЯК Іван Михайлович' => [
                'genitive' => 'старшого лейтенанта ДЖУРЯКА Івана Михайловича',
                'dative' => 'старшому лейтенанту ДЖУРЯКУ Івану Михайловичу',
                'accusative' => 'старшого лейтенанта ДЖУРЯКА Івана Михайловича',
                'instrumental' => 'старшим лейтенантом ДЖУРЯКОМ Іваном Михайловичем',
                'locative' => 'старшому лейтенанту ДЖУРЯКУ Івану Михайловичу',
                'vocative' => 'старший лейтенанте ДЖУРЯК Івану Михайловичу',
            ],
            'старший лейтенант СЛАБКИЙ Руслан Юрійович' => [
                'genitive' => 'старшого лейтенанта СЛАБКОГО Руслана Юрійовича',
                'dative' => 'старшому лейтенанту СЛАБКОМУ Руслану Юрійовичу',
                'accusative' => 'старшого лейтенанта СЛАБКОГО Руслана Юрійовича',
                'instrumental' => 'старшим лейтенантом СЛАБКИМ Русланом Юрійовичем',
                'locative' => 'старшому лейтенанту СЛАБКОМУ Руслану Юрійовичу',
                'vocative' => 'старший лейтенанте СЛАБКИЙ Руслане Юрійовичу',
            ],
            'старший солдат СМОЛЯРОВ Олександр Юрійович' => [
                'genitive' => 'старшого солдата СМОЛЯРОВА Олександра Юрійовича',
                'dative' => 'старшому солдату СМОЛЯРОВУ Олександру Юрійовичу',
                'accusative' => 'старшого солдата СМОЛЯРОВА Олександра Юрійовича',
                'instrumental' => 'старшим солдатом СМОЛЯРОВИМ Олександром Юрійовичем',
                'locative' => 'старшому солдату СМОЛЯРОВУ Олександру Юрійовичу',
                'vocative' => 'старший солдате СМОЛЯРОВ Олександре Юрійовичу',
            ]
        ];

        foreach ($names as $name => $cases) {
            yield "{$name} (nominative)" => [$name, GrammaticalCase::NOMINATIVE, $name];
            foreach ($cases as $case => $expected) {
                yield "{$name} ({$case})" => [$name, GrammaticalCase::from($case), $expected];
            }
        }
    }
} 