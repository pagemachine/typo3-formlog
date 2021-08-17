<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\Form\Finishers;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Domain\Form\Finishers\LoggerFinisher;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\FinisherContext;
use TYPO3\CMS\Form\Domain\Finishers\FinisherVariableProvider;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;
use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Testcase for Pagemachine\Formlog\Domain\Form\Finishers\LoggerFinisher
 */
class LoggerFinisherTest extends UnitTestCase
{
    protected LoggerFinisher $loggerFinisher;

    /**
     * @var FinisherContext|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $finisherContext;

    /**
     * @var Connection|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $connection;

    protected TypoScriptFrontendController $frontendController;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        $formDefinition = $this->prophesize(FormDefinition::class);
        $formDefinition->getIdentifier()->willReturn('test-form');

        $formRuntime = $this->prophesize(FormRuntime::class);
        $formRuntime->getFormDefinition()->willReturn($formDefinition->reveal());

        $this->finisherContext = $this->prophesize(FinisherContext::class);
        $this->finisherContext->getFormRuntime()->willReturn($formRuntime->reveal());

        $this->connection = $this->prophesize(Connection::class);
        $connectionPool = $this->prophesize(ConnectionPool::class);
        $connectionPool->getConnectionForTable('tx_formlog_entries')->willReturn($this->connection->reveal());
        GeneralUtility::addInstance(ConnectionPool::class, $connectionPool->reveal());

        $this->frontendController = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $this->frontendController->id = '2';

        $dateAspect = GeneralUtility::makeInstance(DateTimeAspect::class, new \DateTimeImmutable('@1490191502'));
        $languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, 20);
        $context = GeneralUtility::makeInstance(Context::class, [
            'date' => $dateAspect,
            'language' => $languageAspect,
        ]);
        GeneralUtility::setSingletonInstance(Context::class, $context);

        $this->loggerFinisher = new LoggerFinisher('test', $this->frontendController);
    }

    /**
     * Tear down this testcase
     */
    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
        unset($GLOBALS['EXEC_TIME']);
    }

    /**
     * @dataProvider formValuesToDatabase
     * @test
     */
    public function logsFormValuesToDatabase(array $formValues, array $expectedData)
    {
        $this->finisherContext->getFormValues()->willReturn($formValues);
        $this->finisherContext->getFinisherVariableProvider()->willReturn(new FinisherVariableProvider());

        $this->connection->insert('tx_formlog_entries', $expectedData)->shouldBeCalled();

        $this->loggerFinisher->execute($this->finisherContext->reveal());
    }

    /**
     * @return \Traversable
     */
    public function formValuesToDatabase()
    {
        yield 'Plain form values' => [
            [
                'foo' => 'bar',
                'qux' => 10,
            ],
            [
                'pid' => 2,
                'crdate' => 1490191502,
                'tstamp' => 1490191502,
                'language' => 20,
                'identifier' => 'test-form',
                'data' => '{"foo":"bar","qux":10}',
                'finisher_variables' => '[]',
            ],
        ];

        $fileReference = $this->prophesize(FileReference::class);
        $fileReference->getName()->willReturn('filename.jpg');

        yield 'Form values with file upload' => [
            [
                'foo' => 'bar',
                'qux' => 10,
                'fileupload' => $fileReference->reveal(),
            ],
            [
                'pid' => 2,
                'crdate' => 1490191502,
                'tstamp' => 1490191502,
                'language' => 20,
                'identifier' => 'test-form',
                'data' => '{"foo":"bar","qux":10,"fileupload":{"file":{"name":"filename.jpg"}}}',
                'finisher_variables' => '[]',
            ],
        ];

        yield 'Form values with unsupported object type' => [
            [
                'foo' => 'bar',
                'qux' => 10,
                'object' => new class {
                },
            ],
            [
                'pid' => 2,
                'crdate' => 1490191502,
                'tstamp' => 1490191502,
                'language' => 20,
                'identifier' => 'test-form',
                'data' => '{"foo":"bar","qux":10}',
                'finisher_variables' => '[]',
            ],
        ];
    }

    /**
     * @test
     */
    public function addsFinisherVariables()
    {
        $this->finisherContext->getFormValues()->willReturn(['foo' => 'bar', 'qux' => 10]);
        $variableProvider = new FinisherVariableProvider();
        $variableProvider->add('FinisherA', 'optionA1', 'valueA1');
        $variableProvider->add('FinisherA', 'optionA2', 'valueA2');
        $variableProvider->add('FinisherB', 'optionB1', 'valueB1');
        $this->finisherContext->getFinisherVariableProvider()->willReturn($variableProvider);

        $this->loggerFinisher->setOption('finisherVariables', [
            'FinisherA' => [
                'optionA1',
                'optionA2',
            ],
            'FinisherB' => [
                'optionB1',
            ],
        ]);

        $this->connection->insert('tx_formlog_entries', [
            'pid' => 2,
            'crdate' => 1490191502,
            'tstamp' => 1490191502,
            'language' => 20,
            'identifier' => 'test-form',
            'data' => '{"foo":"bar","qux":10}',
            'finisher_variables' => '{"FinisherA":{"optionA1":"valueA1","optionA2":"valueA2"},"FinisherB":{"optionB1":"valueB1"}}',
        ])->shouldBeCalled();

        $this->loggerFinisher->execute($this->finisherContext->reveal());
    }
}
