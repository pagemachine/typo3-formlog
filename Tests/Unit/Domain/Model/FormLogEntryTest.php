<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\Model;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Domain\Model\FormLogEntry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for Pagemachine\Formlog\Domain\Model\FormLogEntry
 */
class FormLogEntryTest extends UnitTestCase
{
    protected FormLogEntry $formLogEntry;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $this->formLogEntry = new FormLogEntry();
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
    public function decodesData()
    {
        $this->inject($this->formLogEntry, 'data', '{"foo":"bar","qux":10}');

        $expected = [
            'foo' => 'bar',
            'qux' => 10,
        ];

        $this->assertEquals($expected, $this->formLogEntry->getData());
    }

    /**
     * @test
     */
    public function skipsInvalidData()
    {
        $this->assertEquals([], $this->formLogEntry->getData());
    }

    /**
     * @test
     */
    public function decodesFinisherVariables()
    {
        $this->inject($this->formLogEntry, 'finisherVariables', '{"foo":{"bar":"test"},"qux":10}');

        $expected = [
            'foo' => [
                'bar' => 'test',
            ],
            'qux' => 10,
        ];

        $this->assertEquals($expected, $this->formLogEntry->getFinisherVariables());
    }

    /**
     * @test
     */
    public function skipsInvalidFinisherVariables()
    {
        $this->assertEquals([], $this->formLogEntry->getFinisherVariables());
    }
}
