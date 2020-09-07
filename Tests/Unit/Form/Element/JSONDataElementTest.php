<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Form\Element;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Form\Element\JSONDataElement;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService as CoreLanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService as LegacyLanguageService;

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
        $languageService = $this->prophesizeLanguageService();
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
<tr><th rowspan="1">foo</th><td>bar</td></tr>
<tr><th rowspan="1">qux</th><td>10</td></tr>
</table>
HTML;
        yield 'simple' => ['{"foo":"bar","qux":10}', $expected];

        $expected = <<<HTML
<table class="table table-striped table-hover">
<tr><th>Field</th><th>Value</th></tr>
<tr><th rowspan="2">foo</th><td>bar</td></tr>
<tr><td>qux</td></tr>
</table>
HTML;
        yield 'list of values' => ['{"foo":["bar","qux"]}', $expected];

        $expected = <<<HTML
<table class="table table-striped table-hover">
<tr><th>Field</th><th>Value</th></tr>
<tr><th rowspan="1">foo.bar</th><td>qux</td></tr>
<tr><th rowspan="2">foo.list</th><td>first</td></tr>
<tr><td>second</td></tr>
</table>
HTML;
        yield 'nested values' => ['{"foo":{"bar": "qux","list":["first","second"]}}', $expected];
    }

    private function prophesizeLanguageService(): ObjectProphecy
    {
        if (class_exists(CoreLanguageService::class)) {
            return $this->prophesize(CoreLanguageService::class);
        }

        return $this->prophesize(LegacyLanguageService::class);
    }
}
