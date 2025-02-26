<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */
use Pagemachine\Formlog\Domain\FormLog\DateRangeFilter;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Domain\FormLog\DateRangeFilter
 */
class DateRangeFilterTest extends UnitTestCase
{
    /**
     * Tear down this testcase
     */
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    #[Test]
    public function constructsWithDates(): void
    {
        $date1 = new \DateTime();
        $date2 = new \DateTime();
        $dateRangeFilter = new DateRangeFilter($date1, $date2);

        self::assertSame($date1, $dateRangeFilter->getStartDate());
        self::assertSame($date2, $dateRangeFilter->getEndDate());
    }

    #[Test]
    public function isEmptyByDefault(): void
    {
        $dateRangeFilter = new DateRangeFilter();

        self::assertTrue($dateRangeFilter->isEmpty());
    }

    #[Test]
    public function isNotEmptyWithAtLeastOneDate(): void
    {
        $date1 = new \DateTime();
        $date2 = new \DateTime();

        $dateRangeFilter = new DateRangeFilter($date1);

        self::assertFalse($dateRangeFilter->isEmpty());

        $dateRangeFilter = new DateRangeFilter(null, $date2);

        self::assertFalse($dateRangeFilter->isEmpty());

        $dateRangeFilter = new DateRangeFilter($date1, $date2);

        self::assertFalse($dateRangeFilter->isEmpty());
    }

    #[Test]
    public function canBeConvertedToArray(): void
    {
        $dateRangeFilter = new DateRangeFilter(
            new \DateTime('@1601379000'),
            new \DateTime('@1601292600')
        );

        $result = $dateRangeFilter->toArray();
        $expected = [
            'startDate' => '2020-09-29T11:30:00+00:00',
            'endDate' => '2020-09-28T11:30:00+00:00',
        ];

        self::assertEquals($expected, $result);
    }
}
