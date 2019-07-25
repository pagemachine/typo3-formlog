<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\ViewHelpers\Iterable;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use Pagemachine\Formlog\ViewHelpers\Iterator\PaginateViewHelper;
use PHPUnit\Framework\Constraint\ArraySubset as ArraySubsetConstraint;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Testcase for Pagemachine\Formlog\ViewHelpers\Iterator\PaginateViewHelper
 */
class PaginateViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var PaginateViewHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paginateViewHelper;

    /**
     * @var \TYPO3\CMS\Fluid\Core\Variables\CmsVariableProvider|\TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $templateVariableContainer;

    /**
     * Set up this testcase
     */
    protected function setUp()
    {
        parent::setUp();

        $this->paginateViewHelper = $this->getMockBuilder(PaginateViewHelper::class)
            ->setMethods(['renderChildren'])
            ->getMock();
        $this->injectDependenciesIntoViewHelper($this->paginateViewHelper);
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
     * @dataProvider validIterables
     *
     * @param mixed $iterable
     * @param mixed $expectedIterable
     */
    public function supportsValidIterables($iterable, $expectedIterable)
    {
        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', $expectedIterable],
            ['pagination', $this->isType('array')]
        );
        $this->templateVariableContainer->expects($this->exactly(2))->method('remove')->withConsecutive(
            ['pagination'],
            ['paginatedIterable']
        );

        $output = PaginateViewHelper::renderStatic(
            [
                'iterable' => $iterable,
                'as' => 'paginatedIterable',
                'pagination' => 'pagination',
                'currentPage' => 1,
                'itemsPerPage' => 10,
                'maximumNumberOfLinks' => 10,
            ],
            function () {
                return 'test';
            },
            $this->renderingContext
        );

        $this->assertEquals('test', $output);
    }

    /**
     * @return \Traversable
     */
    public function validIterables()
    {
        yield 'array' => [
            range(1, 30),
            range(1, 10),
        ];

        $storage = new ObjectStorage();
        $objectFactory = function ($i) use ($storage) {
            $object = new \stdClass();
            $object->identifier = $i;
            $storage->attach($object);

            return $object;
        };
        $allObjects = array_map($objectFactory, range(1, 30));
        $paginatedObjects = array_slice($allObjects, 0, 10);

        yield 'ObjectStorage' => [$storage, $paginatedObjects];

        /** @var QueryResultInterface|\Prophecy\Prophecy\ObjectProphecy */
        $paginatedQueryResult = $this->prophesize(QueryResultInterface::class);

        /** @var QueryInterface|\Prophecy\Prophecy\ObjectProphecy */
        $query = $this->prophesize(QueryInterface::class);
        $query->setLimit(10)->shouldBeCalled()->willReturn($query->reveal());
        $query->setOffset(0)->shouldBeCalled()->willReturn($query->reveal());
        $query->execute()->willReturn($paginatedQueryResult->reveal());

        /** @var QueryResultInterface|\Prophecy\Prophecy\ObjectProphecy */
        $queryResult = $this->prophesize(QueryResultInterface::class);
        $queryResult->getQuery()->willReturn($query->reveal());
        $queryResult->count()->willReturn(30);
        $queryResult->rewind()->willReturn();
        $queryResult->valid()->willReturn(...array_merge(array_fill(0, 30, true), [false]));
        $queryResult->current()->willReturn(null);
        $queryResult->key()->willReturn(0);
        $queryResult->next()->willReturn();

        yield 'QueryResultInterface' => [$queryResult->reveal(), $paginatedQueryResult->reveal()];

        /** @var QueryInterface|\Prophecy\Prophecy\ObjectProphecy */
        $query = $this->prophesize(QueryInterface::class);
        $query->setLimit(10)->shouldBeCalled()->willReturn($query->reveal());
        $query->setOffset(0)->shouldBeCalled()->willReturn($query->reveal());
        $query->execute()->willReturn($paginatedQueryResult->reveal());

        /** @var QueryResultInterface|\Prophecy\Prophecy\ObjectProphecy */
        $queryResult = $this->prophesize(QueryResultInterface::class);
        $queryResult->getQuery()->willReturn($query->reveal());
        $queryResult->count()->willReturn(0);
        $queryResult->rewind()->willReturn();

        yield 'empty QueryResultInterface' => [$queryResult->reveal(), $paginatedQueryResult->reveal()];
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidIterables()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1516182434);

        PaginateViewHelper::renderStatic(
            [
                'iterable' => new \stdClass(),
            ],
            function () {
                return 'test';
            },
            $this->renderingContext
        );
    }

    /**
     * @test
     * @dataProvider paginatedIterable
     *
     * @param int $currentPage
     * @param array $expectedIterable
     * @param array $expectedPagination
     */
    public function paginatesIterable($currentPage, array $expectedIterable, array $expectedPagination)
    {
        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', $expectedIterable],
            ['pagination', new ArraySubsetConstraint($expectedPagination)]
        );

        PaginateViewHelper::renderStatic(
            [
                'iterable' => range(1, 500),
                'as' => 'paginatedIterable',
                'pagination' => 'pagination',
                'currentPage' => $currentPage,
                'itemsPerPage' => 10,
                'maximumNumberOfLinks' => 10,
            ],
            function () {
                return null;
            },
            $this->renderingContext
        );
    }

    /**
     * @return \Traversable
     */
    public function paginatedIterable()
    {
        $firstPagePagination = [
            'current' => 1,
            'numberOfPages' => 50,
            'displayRangeStart' => 1,
            'displayRangeEnd' => 10,
            'hasLessPages' => false,
            'hasMorePages' => true,
            'nextPage' => 2,
        ];
        yield 'first page' => [
            1,
            range(1, 10),
            $firstPagePagination,
        ];
        yield 'before first page' => [
            -1,
            range(1, 10),
            $firstPagePagination,
        ];

        yield 'middle page' => [
            32,
            range(311, 320),
            [
                'current' => 32,
                'numberOfPages' => 50,
                'displayRangeStart' => 27,
                'displayRangeEnd' => 36,
                'hasLessPages' => true,
                'hasMorePages' => true,
                'previousPage' => 31,
                'nextPage' => 33,
            ],
        ];

        $lastPagePagination = [
            'current' => 50,
            'numberOfPages' => 50,
            'displayRangeStart' => 41,
            'displayRangeEnd' => 50,
            'hasLessPages' => true,
            'hasMorePages' => false,
            'previousPage' => 49,
        ];
        yield 'last page' => [
            50,
            range(491, 500),
            $lastPagePagination,
        ];
        yield 'after last page' => [
            51,
            range(491, 500),
            $lastPagePagination,
        ];
    }

    /**
     * @test
     */
    public function allowsCustomItemsPerPage()
    {
        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', range(1, 50)],
            ['pagination', new ArraySubsetConstraint([
                'numberOfPages' => 10,
            ])]
        );

        PaginateViewHelper::renderStatic(
            [
                'iterable' => range(1, 500),
                'as' => 'paginatedIterable',
                'pagination' => 'pagination',
                'currentPage' => 1,
                'itemsPerPage' => 50,
                'maximumNumberOfLinks' => 10,
            ],
            function () {
                return null;
            },
            $this->renderingContext
        );
    }
    /**
     * @test
     */
    public function allowsCustomMaximumNumberOfLinks()
    {
        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', range(1, 10)],
            ['pagination', new ArraySubsetConstraint([
                'numberOfPages' => 50,
                'displayRangeStart' => 1,
                'displayRangeEnd' => 5,
            ])]
        );

        PaginateViewHelper::renderStatic(
            [
                'iterable' => range(1, 500),
                'as' => 'paginatedIterable',
                'pagination' => 'pagination',
                'currentPage' => 1,
                'itemsPerPage' => 10,
                'maximumNumberOfLinks' => 5,
            ],
            function () {
                return null;
            },
            $this->renderingContext
        );
    }
}
