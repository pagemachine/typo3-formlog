<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\ViewHelpers\Format;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Rendering\ValueFormatter;

/**
 * Testcase for Pagemachine\Formlog\Rendering\ValueFormatter
 */
final class ValueFormatterTest extends UnitTestCase
{
    /**
     * @var ValueFormatter
     */
    protected $valueFormatter;

    /**
     * Set up this testcase
     */
    protected function setUp()
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
foo
bar
qux
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
