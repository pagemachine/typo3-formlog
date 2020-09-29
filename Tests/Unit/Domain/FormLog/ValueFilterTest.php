<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Domain\FormLog\ValueFilter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\Formlog\Domain\FormLog\ValueFilter
 *
 * @requires PHP 7
 */
class ValueFilterTest extends UnitTestCase
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
    public function constructsWithValue()
    {
        $valueFilter = new ValueFilter('foo');

        $this->assertEquals('foo', $valueFilter->getValue());
    }

    /**
     * @test
     */
    public function isEmptyByDefault()
    {
        $valueFilter = new ValueFilter();

        $this->assertTrue($valueFilter->isEmpty());
    }

    /**
     * @test
     */
    public function isNotEmptyWithValue()
    {
        $valueFilter = new ValueFilter('foo');

        $this->assertFalse($valueFilter->isEmpty());
    }

    /**
     * @test
     */
    public function canBeConvertedToArray(): void
    {
        $valueFilter = new ValueFilter('foo');

        $result = $valueFilter->toArray();
        $expected = [
            'value' => 'foo',
        ];

        $this->assertEquals($expected, $result);
    }
}
