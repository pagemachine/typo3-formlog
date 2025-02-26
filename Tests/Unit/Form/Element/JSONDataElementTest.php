<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit\Form\Element;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */
use Pagemachine\Formlog\Form\Element\JSONDataElement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Form\Element\JSONDataElement
 */
class JSONDataElementTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * Tear down this testcase
     */
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @param string $formElementValue
     * @param string $expected
     */
    #[DataProvider('samples')]
    #[Test]
    public function rendersFormData($formElementValue, $expected): void
    {
        if ((new Typo3Version())->getMajorVersion() < 13) {
            GeneralUtility::addInstance(IconFactory::class, $this->prophesize(IconFactory::class)->reveal());

            $jsonDataElement = new JSONDataElement($this->prophesize(NodeFactory::class)->reveal(), [

                'parameterArray' => [
                    'itemFormElValue' => $formElementValue,
                ],
            ]);
        } else {
            $jsonDataElement = new JSONDataElement();
            $jsonDataElement->setData([
                'parameterArray' => [
                    'itemFormElValue' => $formElementValue,
                ],
            ]);
        }

        $languageService = $this->prophesize(LanguageService::class);
        $languageService->sL(Argument::containingString('field'))->willReturn('Field');
        $languageService->sL(Argument::containingString('value'))->willReturn('Value');
        $GLOBALS['LANG'] = $languageService->reveal();

        $result = $jsonDataElement->render();

        self::assertEquals($expected, $result['html']);
    }

    public static function samples(): \Generator
    {
        $expected = <<<HTML
<table class="table table-striped table-hover">
<tr><th>Field</th><th>Value</th></tr>
<tr><th>foo</th><td style="white-space: pre">bar</td></tr>
<tr><th>qux</th><td style="white-space: pre">10</td></tr>
</table>
HTML;
        yield 'simple' => ['{"foo":"bar","qux":10}', $expected];

        $expected = <<<HTML
<table class="table table-striped table-hover">
<tr><th>Field</th><th>Value</th></tr>
<tr><th>foo</th><td style="white-space: pre">0: bar
1: qux</td></tr>
</table>
HTML;
        yield 'list of values' => ['{"foo":["bar","qux"]}', $expected];

        $expected = <<<HTML
<table class="table table-striped table-hover">
<tr><th>Field</th><th>Value</th></tr>
<tr><th>foo</th><td style="white-space: pre">bar: qux
list:
    0: first
    1: second</td></tr>
</table>
HTML;
        yield 'nested values' => ['{"foo":{"bar": "qux","list":["first","second"]}}', $expected];
    }
}
