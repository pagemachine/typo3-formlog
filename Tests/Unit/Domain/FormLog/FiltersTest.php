<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */
use Pagemachine\Formlog\Domain\FormLog\DateRangeFilter;
use Pagemachine\Formlog\Domain\FormLog\Filters;
use Pagemachine\Formlog\Domain\FormLog\ValueFilter;
use PHPUnit\Framework\Attributes\Test;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Domain\FormLog\Filters
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

    #[Test]
    public function constructsWithFilters(): void
    {
        $pageTitleFilter = $this->prophesize(ValueFilter::class)->reveal();
        $identifierFilter = $this->prophesize(ValueFilter::class)->reveal();
        $submissionDateFilter = $this->prophesize(DateRangeFilter::class)->reveal();
        $filters = new Filters($pageTitleFilter, $identifierFilter, $submissionDateFilter);

        self::assertSame($pageTitleFilter, $filters->getPageTitle());
        self::assertSame($identifierFilter, $filters->getIdentifier());
        self::assertSame($submissionDateFilter, $filters->getSubmissionDate());
    }

    #[Test]
    public function constructsWithFilterDefaults(): void
    {
        $filters = new Filters();

        self::assertInstanceOf(ValueFilter::class, $filters->getPageTitle());
        self::assertInstanceOf(DateRangeFilter::class, $filters->getSubmissionDate());
    }

    #[Test]
    public function isEmptyByDefault(): void
    {
        $filters = new Filters();

        self::assertTrue($filters->isEmpty());
    }

    #[Test]
    public function isNotEmptyWithAtLeastOneNonEmptyFilter(): void
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

    #[Test]
    public function yieldsNothingOnTraversalByDefault(): void
    {
        $filters = new Filters();
        $items = iterator_to_array($filters);

        self::assertEmpty($items);
    }

    #[Test]
    public function yieldsNonEmptyFiltersOnTraversal(): void
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

    #[Test]
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
