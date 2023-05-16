<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Mvc\View;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Mvc\View\FormatViewResolver;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Mvc\View\FormatViewResolver
 */
final class FormatViewResolverTest extends UnitTestCase
{
    use ProphecyTrait;

    protected FormatViewResolver $formatViewResolver;

    /**
     * Set up this testcase
     */
    protected function setUp(): void
    {
        $this->formatViewResolver = new FormatViewResolver();
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    /**
     * @test
     */
    public function returnsTemplateViewForUnknownFormats(): void
    {
        GeneralUtility::addInstance(TemplateView::class, $this->prophesize(TemplateView::class)->reveal());
        
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
