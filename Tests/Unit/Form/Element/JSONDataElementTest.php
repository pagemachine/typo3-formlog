<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Form\Element;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Form\Element\JSONDataElement;
use Prophecy\Argument;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\Formlog\Form\Element\JSONDataElement
 */
class JSONDataElementTest extends UnitTestCase
{
    /**
     * Tear down this testcase
     */
    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     * @dataProvider samples
     *
     * @param string $formElementValue
     * @param string $expected
     */
    public function rendersFormData($formElementValue, $expected)
    {
        $iconFactory = $this->prophesize(IconFactory::class);
        GeneralUtility::addInstance(IconFactory::class, $iconFactory->reveal());

        $nodeFactory = $this->prophesize(NodeFactory::class);
        $jsonDataElement = new JSONDataElement($nodeFactory->reveal(), [
            'parameterArray' => [
                'itemFormElValue' => $formElementValue,
            ],
        ]);
        $languageService = $this->prophesize(LanguageService::class);
        $languageService->sL(Argument::containingString('field'))->willReturn('Field');
        $languageService->sL(Argument::containingString('value'))->willReturn('Value');
        $GLOBALS['LANG'] = $languageService->reveal();

        $result = $jsonDataElement->render();

        $this->assertEquals($expected, $result['html']);
    }

    public function samples()
    {
        $expected = <<<HTML
<table class="table table-striped table-hover">
<tr><th>Field</th><th>Value</th></tr>
</table>
HTML;
        yield 'empty' => ['', $expected];

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
<tr><th>foo</th><td style="white-space: pre">bar
qux</td></tr>
</table>
HTML;
        yield 'list of values' => ['{"foo":["bar","qux"]}', $expected];

        $expected = <<<HTML
<table class="table table-striped table-hover">
<tr><th>Field</th><th>Value</th></tr>
<tr><th>foo</th><td style="white-space: pre">bar: qux
list:
    first
    second</td></tr>
</table>
HTML;
        yield 'nested values' => ['{"foo":{"bar": "qux","list":["first","second"]}}', $expected];
    }
}
