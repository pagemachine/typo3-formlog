<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\FormLog\DateRangeFilter;
use Pagemachine\Formlog\Domain\FormLog\Filters;
use Pagemachine\Formlog\Domain\FormLog\ValueFilter;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

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

        self::assertSame($pageTitleFilter, $filters->getPageTitle());
        self::assertSame($identifierFilter, $filters->getIdentifier());
        self::assertSame($submissionDateFilter, $filters->getSubmissionDate());
    }

    /**
     * @test
     */
    public function constructsWithFilterDefaults()
    {
        $filters = new Filters();

        self::assertInstanceOf(ValueFilter::class, $filters->getPageTitle());
        self::assertInstanceOf(DateRangeFilter::class, $filters->getSubmissionDate());
    }

    /**
     * @test
     */
    public function isEmptyByDefault()
    {
        $filters = new Filters();

        self::assertTrue($filters->isEmpty());
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

        self::assertFalse($filters->isEmpty());

        $filters = new Filters(null, null, $submissionDateFilter->reveal());

        self::assertFalse($filters->isEmpty());

        $filters = new Filters($pageTitleFilter->reveal(), null, $submissionDateFilter->reveal());

        self::assertFalse($filters->isEmpty());
    }

    /**
     * @test
     */
    public function yieldsNothingOnTraversalByDefault()
    {
        $filters = new Filters();
        $items = iterator_to_array($filters);

        self::assertEmpty($items);
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

        self::assertCount(1, $items);
        self::assertContainsOnlyInstancesOf(ValueFilter::class, $items);

        $filters = new Filters(null, null, $submissionDateFilter->reveal());
        $items = iterator_to_array($filters);

        self::assertCount(1, $items);
        self::assertContainsOnlyInstancesOf(DateRangeFilter::class, $items);

        $filters = new Filters($pageTitleFilter->reveal(), $identifierFilter->reveal(), $submissionDateFilter->reveal());
        $items = iterator_to_array($filters);

        self::assertCount(2, $items);
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

        self::assertArrayHasKey('pageTitle', $result);
        self::assertIsArray($result['pageTitle']);
        self::assertArrayHasKey('identifier', $result);
        self::assertIsArray($result['identifier']);
        self::assertArrayHasKey('submissionDate', $result);
        self::assertIsArray($result['submissionDate']);
    }
}
