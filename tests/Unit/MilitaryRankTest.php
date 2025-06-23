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
        ];

        foreach ($names as $name => $cases) {
            yield "{$name} (nominative)" => [$name, GrammaticalCase::NOMINATIVE, $name];
            foreach ($cases as $case => $expected) {
                yield "{$name} ({$case})" => [$name, GrammaticalCase::from($case), $expected];
            }
        }
    }
} 