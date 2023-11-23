<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\ViewHelpers\Format;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Rendering\ValueFormatter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Rendering\ValueFormatter
 */
final class ValueFormatterTest extends UnitTestCase
{
    protected ValueFormatter $valueFormatter;

    /**
     * Set up this testcase
     */
    protected function setUp(): void
    {
        $this->valueFormatter = new ValueFormatter();
    }
    /**
     * @test
     * @dataProvider formValues
     */
    public function formatsSupportedValues($value, string $expected): void
    {
        $result = $this->valueFormatter->format($value);

        $this->assertEquals($expected, $result);
    }

    public function formValues(): \Generator
    {
        yield 'null' => [
            null,
            '',
        ];

        yield 'string' => [
            'test',
            'test',
        ];

        yield 'number' => [
            9001,
            '9001',
        ];

        yield 'sequential array' => [
            [
                'foo',
                'bar',
                'qux',
            ],
            <<<TEXT
0: foo
1: bar
2: qux
TEXT
,
        ];

        yield 'associative array' => [
            [
                '1st' => 'foo',
                '2nd' => 'bar',
                '3rd' => 'qux',
            ],
            <<<TEXT
1st: foo
2nd: bar
3rd: qux
TEXT
,
        ];

        yield 'nested associative array' => [
            [
                '1st' => [
                    '2nd' => [
                        '3rd' => 'foo',
                    ],
                    '4th' => 'bar',
                ],
            ],
            <<<TEXT
1st:
    2nd:
        3rd: foo
    4th: bar
TEXT
,
        ];

        yield 'array with mixed values' => [
            [
                '1st' => [
                    '2nd' => 'foo',
                    '3rd' => [
                        '4th' => 'bar',
                    ],
                    '5th' => 'qux',
                ],
            ],
            <<<TEXT
1st:
    2nd: foo
    3rd:
        4th: bar
    5th: qux
TEXT
,
        ];

        yield 'array with mixed keys' => [
            [
                [
                    '1st' => 'foo',
                    '2nd' => [],
                ],
                [
                    '1st' => 'bar',
                    '2nd' => [
                        '3rd',
                    ],
                ],
            ],
            <<<TEXT
0:
    1st: foo
    2nd:

1:
    1st: bar
    2nd:
        0: 3rd
TEXT
,
        ];

        yield 'date' => [
            new \DateTime('@1610102035'),
            '2021-01-08T10:33:55+00:00',
        ];

        yield 'immutable date' => [
            new \DateTimeImmutable('@1610102035'),
            '2021-01-08T10:33:55+00:00',
        ];
    }

    /**
     * @test
     */
    public function rendersDateWithCustomFormat(): void
    {
        $date = new \DateTime('@1610102035');

        $result = $this->valueFormatter
            ->setDateTimeFormat('d.m.Y')
            ->format($date);

        $this->assertEquals('08.01.2021', $result);
    }

    /**
     * @test
     */
    public function throwsExceptionOnUnsupportedValues(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->valueFormatter->format(new \stdClass);
    }
}
