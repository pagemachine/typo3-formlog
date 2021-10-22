<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Mvc\View;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Mvc\View\FormatViewResolver;
use Prophecy\Argument;
use Prophecy\Prophet;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * Testcase for Pagemachine\Formlog\Mvc\View\FormatViewResolver
 */
final class FormatViewResolverTest extends UnitTestCase
{
    protected FormatViewResolver $formatViewResolver;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->objectManager = $this->prophesize(ObjectManager::class);
        $this->objectManager
            ->get(Argument::type('string'))
            ->will(function (array $arguments): object {
                return (new Prophet())->prophesize($arguments[0]);
            });
        $this->formatViewResolver = new FormatViewResolver($this->objectManager->reveal());
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     */
    public function returnsTemplateViewForUnknownFormats(): void
    {
        $result = $this->formatViewResolver->resolve('TestController', 'testAction', 'foo');

        $this->assertInstanceOf(TemplateView::class, $result);
    }

    /**
     * @test
     */
    public function returnsViewForMappedFormat(): void
    {
        $fooView = $this->prophesize(ViewInterface::class)->reveal();
        $barView = $this->prophesize(ViewInterface::class)->reveal();
        $this->formatViewResolver->map('foo', get_class($fooView));
        $this->formatViewResolver->map('bar', get_class($barView));

        $result = $this->formatViewResolver->resolve('TestController', 'testAction', 'foo');

        $this->assertInstanceOf(get_class($fooView), $result);

        $result = $this->formatViewResolver->resolve('TestController', 'testAction', 'bar');

        $this->assertInstanceOf(get_class($barView), $result);
    }
}
