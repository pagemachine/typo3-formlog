<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Domain\FormLog\DateRangeFilter;
use Pagemachine\Formlog\Domain\FormLog\Filters;
use Pagemachine\Formlog\Domain\FormLog\ValueFilter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\Formlog\Domain\FormLog\Filters
 *
 * @requires PHP 7
 */
class FiltersTest extends UnitTestCase
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
     */
    public function constructsWithFilters()
    {
        /** @var ValueFilter */
        $pageTitleFilter = $this->prophesize(ValueFilter::class)->reveal();
        /** @var ValueFilter */
        $identifierFilter = $this->prophesize(ValueFilter::class)->reveal();
        /** @var DateRangeFilter */
        $submissionDateFilter = $this->prophesize(DateRangeFilter::class)->reveal();
        $filters = new Filters($pageTitleFilter, $identifierFilter, $submissionDateFilter);

        $this->assertSame($pageTitleFilter, $filters->getPageTitle());
        $this->assertSame($identifierFilter, $filters->getIdentifier());
        $this->assertSame($submissionDateFilter, $filters->getSubmissionDate());
    }

    /**
     * @test
     */
    public function constructsWithFilterDefaults()
    {
        $filters = new Filters();

        $this->assertInstanceOf(ValueFilter::class, $filters->getPageTitle());
        $this->assertInstanceOf(DateRangeFilter::class, $filters->getSubmissionDate());
    }

    /**
     * @test
     */
    public function isEmptyByDefault()
    {
        $filters = new Filters();

        $this->assertTrue($filters->isEmpty());
    }

    /**
     * @test
     */
    public function isNotEmptyWithAtLeastOneNonEmptyFilter()
    {
        /** @var ValueFilter|\Prophecy\Prophecy\ObjectProphecy */
        $pageTitleFilter = $this->prophesize(ValueFilter::class);
        $pageTitleFilter->isEmpty()->willReturn(false);
        /** @var DateRangeFilter|\Prophecy\Prophecy\ObjectProphecy */
        $submissionDateFilter = $this->prophesize(DateRangeFilter::class);
        $submissionDateFilter->isEmpty()->willReturn(false);

        $filters = new Filters($pageTitleFilter->reveal());

        $this->assertFalse($filters->isEmpty());

        $filters = new Filters(null, null, $submissionDateFilter->reveal());

        $this->assertFalse($filters->isEmpty());

        $filters = new Filters($pageTitleFilter->reveal(), null, $submissionDateFilter->reveal());

        $this->assertFalse($filters->isEmpty());
    }

    /**
     * @test
     */
    public function yieldsNothingOnTraversalByDefault()
    {
        $filters = new Filters();
        $items = iterator_to_array($filters);

        $this->assertEmpty($items);
    }

    /**
     * @test
     */
    public function yieldsNonEmptyFiltersOnTraversal()
    {
        /** @var ValueFilter|\Prophecy\Prophecy\ObjectProphecy */
        $pageTitleFilter = $this->prophesize(ValueFilter::class);
        $pageTitleFilter->isEmpty()->willReturn(false);
        /** @var ValueFilter|\Prophecy\Prophecy\ObjectProphecy */
        $identifierFilter = $this->prophesize(ValueFilter::class);
        $identifierFilter->isEmpty()->willReturn(true);
        /** @var DateRangeFilter|\Prophecy\Prophecy\ObjectProphecy */
        $submissionDateFilter = $this->prophesize(DateRangeFilter::class);
        $submissionDateFilter->isEmpty()->willReturn(false);

        $filters = new Filters($pageTitleFilter->reveal());
        $items = iterator_to_array($filters);

        $this->assertCount(1, $items);
        $this->assertContainsOnlyInstancesOf(ValueFilter::class, $items);

        $filters = new Filters(null, null, $submissionDateFilter->reveal());
        $items = iterator_to_array($filters);

        $this->assertCount(1, $items);
        $this->assertContainsOnlyInstancesOf(DateRangeFilter::class, $items);

        $filters = new Filters($pageTitleFilter->reveal(), $identifierFilter->reveal(), $submissionDateFilter->reveal());
        $items = iterator_to_array($filters);

        $this->assertCount(2, $items);
    }
}
