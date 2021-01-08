<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\ViewHelpers\Format;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\ViewHelpers\Format\FormValueViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\Formlog\ViewHelpers\Format\FormValueViewHelper
 */
final class FormValueViewHelperTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider formValues
     */
    public function formatsSupportedValues($value, string $expected): void
    {
        $renderChildrenClosure = function () use ($value) {
            return $value;
        };

        $result = FormValueViewHelper::renderStatic(
            [],
            $renderChildrenClosure,
            $this->prophesize(RenderingContextInterface::class)->reveal()
        );

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
        $renderChildrenClosure = function () use ($value) {
            return new \stdClass;
        };

        $this->expectException(\UnexpectedValueException::class);

        FormValueViewHelper::renderStatic(
            [],
            $renderChildrenClosure,
            $this->prophesize(RenderingContextInterface::class)->reveal()
        );
    }
}
