<?php
declare(strict_types = 1);
namespace Pagemachine\Formlog\Tests\Unit\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Domain\FormLog\DateRangeFilter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\Formlog\Domain\FormLog\DateRangeFilter
 *
 * @requires PHP 7
 */
class DateRangeFilterTest extends UnitTestCase
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
    public function constructsWithDates()
    {
        $date1 = new \DateTime();
        $date2 = new \DateTime();
        $dateRangeFilter = new DateRangeFilter($date1, $date2);

        $this->assertSame($date1, $dateRangeFilter->getStartDate());
        $this->assertSame($date2, $dateRangeFilter->getEndDate());
    }

    /**
     * @test
     */
    public function isEmptyByDefault()
    {
        $dateRangeFilter = new DateRangeFilter();

        $this->assertTrue($dateRangeFilter->isEmpty());
    }

    /**
     * @test
     */
    public function isNotEmptyWithAtLeastOneDate()
    {
        $date1 = new \DateTime();
        $date2 = new \DateTime();

        $dateRangeFilter = new DateRangeFilter($date1);

        $this->assertFalse($dateRangeFilter->isEmpty());

        $dateRangeFilter = new DateRangeFilter(null, $date2);

        $this->assertFalse($dateRangeFilter->isEmpty());

        $dateRangeFilter = new DateRangeFilter($date1, $date2);

        $this->assertFalse($dateRangeFilter->isEmpty());
    }
}
