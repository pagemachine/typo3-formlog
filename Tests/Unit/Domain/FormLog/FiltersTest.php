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
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\Formlog\Domain\FormLog\Filters
 *
 * @requires PHP 7
 */
class FiltersTest extends UnitTestCase
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
     * @test
     */
    public function constructsWithFilters()
    {
        $pageTitleFilter = $this->prophesize(ValueFilter::class)->reveal();
        $identifierFilter = $this->prophesize(ValueFilter::class)->reveal();
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
        $pageTitleFilter = $this->prophesize(ValueFilter::class);
        $pageTitleFilter->isEmpty()->willReturn(false);
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
        $pageTitleFilter = $this->prophesize(ValueFilter::class);
        $pageTitleFilter->isEmpty()->willReturn(false);
        $identifierFilter = $this->prophesize(ValueFilter::class);
        $identifierFilter->isEmpty()->willReturn(true);
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

    /**
     * @test
     */
    public function canBeConvertedToArray(): void
    {
        $pageTitleFilter = $this->prophesize(ValueFilter::class);
        $pageTitleFilter->toArray()->willReturn(['value' => 'Test']);
        $identifierFilter = $this->prophesize(ValueFilter::class);
        $identifierFilter->toArray()->willReturn(['value' => 'foo']);
        $submissionDateFilter = $this->prophesize(DateRangeFilter::class);
        $submissionDateFilter->toArray()->willReturn(['value' => 'foo']);

        $filters = new Filters(
            new ValueFilter('Test'),
            new ValueFilter('foo'),
            new DateRangeFilter(new \DateTime(), new \DateTime())
        );

        $result = $filters->toArray();

        $this->assertArrayHasKey('pageTitle', $result);
        $this->assertIsArray($result['pageTitle']);
        $this->assertArrayHasKey('identifier', $result);
        $this->assertIsArray($result['identifier']);
        $this->assertArrayHasKey('submissionDate', $result);
        $this->assertIsArray($result['submissionDate']);
    }
}
