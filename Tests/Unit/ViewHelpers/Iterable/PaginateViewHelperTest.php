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
        $this->setArgumentsUnderTest($this->paginateViewHelper, [
            'iterable' => $iterable,
            'as' => 'paginatedIterable',
        ]);
        $this->paginateViewHelper->method('renderChildren')->willReturn('test');

        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', $expectedIterable],
            ['pagination', $this->isType('array')]
        );
        $this->templateVariableContainer->expects($this->exactly(2))->method('remove')->withConsecutive(
            ['pagination'],
            ['paginatedIterable']
        );

        $output = $this->paginateViewHelper->render();

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

        yield 'empty QueryResultInterface' => [$queryResult->reveal(), $paginatedQueryResult->reveal()];
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidIterables()
    {
        $this->setArgumentsUnderTest($this->paginateViewHelper, [
            'iterable' => new \stdClass(),
            'as' => 'paginatedIterable',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1516182434);

        $this->paginateViewHelper->render();
    }

    /**
     * @test
     * @dataProvider paginatedIterable
     *
     * @param int $currentPage
     * @param mixed $expectedIterable
     * @param array $expectedPagination
     */
    public function paginatesIterable($currentPage, array $expectedIterable, array $expectedPagination)
    {
        $this->setArgumentsUnderTest($this->paginateViewHelper, [
            'iterable' => range(1, 500),
            'as' => 'paginatedIterable',
            'currentPage' => $currentPage,
        ]);
        $this->paginateViewHelper->method('renderChildren')->willReturn(null);

        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', $expectedIterable],
            ['pagination', new ArraySubsetConstraint($expectedPagination)]
        );

        $this->paginateViewHelper->render();
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
        $this->setArgumentsUnderTest($this->paginateViewHelper, [
            'iterable' => range(1, 500),
            'as' => 'paginatedIterable',
            'itemsPerPage' => 50,
        ]);
        $this->paginateViewHelper->method('renderChildren')->willReturn(null);

        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', range(1, 50)],
            ['pagination', new ArraySubsetConstraint([
                'numberOfPages' => 10,
            ])]
        );

        $this->paginateViewHelper->render();
    }
    /**
     * @test
     */
    public function allowsCustomMaximumNumberOfLinks()
    {
        $this->setArgumentsUnderTest($this->paginateViewHelper, [
            'iterable' => range(1, 500),
            'as' => 'paginatedIterable',
            'maximumNumberOfLinks' => 5,
        ]);
        $this->paginateViewHelper->method('renderChildren')->willReturn(null);

        $this->templateVariableContainer->expects($this->exactly(2))->method('add')->withConsecutive(
            ['paginatedIterable', range(1, 10)],
            ['pagination', new ArraySubsetConstraint([
                'numberOfPages' => 50,
                'displayRangeStart' => 1,
                'displayRangeEnd' => 5,
            ])]
        );

        $this->paginateViewHelper->render();
    }
}
