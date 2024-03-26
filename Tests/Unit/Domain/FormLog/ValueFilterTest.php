<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */
use Pagemachine\Formlog\Domain\FormLog\ValueFilter;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

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
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
    }

    #[Test]
    public function constructsWithValue()
    {
        $valueFilter = new ValueFilter('foo');

        self::assertEquals('foo', $valueFilter->getValue());
    }

    #[Test]
    public function isEmptyByDefault()
    {
        $valueFilter = new ValueFilter();

        self::assertTrue($valueFilter->isEmpty());
    }

    #[Test]
    public function isNotEmptyWithValue()
    {
        $valueFilter = new ValueFilter('foo');

        self::assertFalse($valueFilter->isEmpty());
    }

    #[Test]
    public function canBeConvertedToArray(): void
    {
        $valueFilter = new ValueFilter('foo');

        $result = $valueFilter->toArray();
        $expected = [
            'value' => 'foo',
        ];

        self::assertEquals($expected, $result);
    }
}
