<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\ViewHelpers\Iterator;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * This viewhelper renders a pagination for iterables
 *
 * = Examples =
 *
 * <code title="required arguments">
 * <fl:iterator.paginate iterable="{blogs}" as="paginatedBlogs">
 * use {paginatedBlogs} as you used {blogs} before, most certainly inside a <f:for> loop.
 * </fl:iterator.paginate>
 * </code>
 *
 * <code title="full configuration">
 * <fl:iterator.paginate iterable="{blogs}" as="paginatedBlogs" currentPage="5" itemsPerPage="5" maximumNumberOfLinks="20">
 * use {paginatedBlogs} as you used {blogs} before, most certainly inside <f:for> loop.
 * </fl:iterator.paginate>
 * </code>
 */
class PaginateViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('iterable', 'mixed', 'Array or iterable & countable object', true);
        $this->registerArgument('as', 'string', 'Variable name for the paginated items', true);
        $this->registerArgument('pagination', 'string', 'Variable name for the pagination', false, 'pagination');
        $this->registerArgument('currentPage', 'int', 'Current pagination page', false, 1);
        $this->registerArgument('itemsPerPage', 'int', 'Maximum number of items per page', false, 10);
        $this->registerArgument('maximumNumberOfLinks', 'int', 'Maximum number of pagination links', false, 10);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $iterable = $arguments['iterable'];
        self::assertIterable($iterable);

        $itemsPerPage = max(1, $arguments['itemsPerPage']);
        $numberOfPages = max(1, ceil(count($iterable) / $itemsPerPage));
        $currentPage = min(max(1, $arguments['currentPage']), $numberOfPages);
        $paginatedIterable = static::sliceIterable($iterable, $itemsPerPage, (int)($itemsPerPage * ($currentPage - 1)));
        list($displayRangeStart, $displayRangeEnd) = static::calculateDisplayRange($currentPage, $numberOfPages, $arguments['maximumNumberOfLinks']);

        $pages = [];

        for ($n = $displayRangeStart; $n <= $displayRangeEnd; ++$n) {
            $pages[] = [
                'number' => $n,
                'isCurrent' => $n == $currentPage,
            ];
        }

        $pagination = [
            'pages' => $pages,
            'current' => $currentPage,
            'numberOfPages' => $numberOfPages,
            'displayRangeStart' => $displayRangeStart,
            'displayRangeEnd' => $displayRangeEnd,
            'hasLessPages' => $displayRangeStart > 2,
            'hasMorePages' => $displayRangeEnd + 1 < $numberOfPages,
        ];

        if ($currentPage > 1) {
            $pagination['previousPage'] = $currentPage - 1;
        }

        if ($currentPage < $numberOfPages) {
            $pagination['nextPage'] = $currentPage + 1;
        }

        $variableProvider = $renderingContext->getVariableProvider();
        $variableProvider->add($arguments['as'], $paginatedIterable);
        $variableProvider->add($arguments['pagination'], $pagination);
        $output = $renderChildrenClosure();
        $variableProvider->remove($arguments['pagination']);
        $variableProvider->remove($arguments['as']);

        return $output;
    }

    /**
     * Calculates the display range given the current pagination state
     *
     * @return array
     */
    protected static function calculateDisplayRange($currentPage, $numberOfPages, $maximumNumberOfLinks)
    {
        $maximumNumberOfLinks = min($maximumNumberOfLinks, $numberOfPages);
        $delta = floor($maximumNumberOfLinks / 2);
        $displayRangeStart = $currentPage - $delta;
        $displayRangeEnd = $currentPage + $delta - ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);

        if ($displayRangeStart < 1) {
            $displayRangeEnd -= $displayRangeStart - 1;
        }

        if ($displayRangeEnd > $numberOfPages) {
            $displayRangeStart -= $displayRangeEnd - $numberOfPages;
        }

        $displayRangeStart = (int) max($displayRangeStart, 1);
        $displayRangeEnd = (int) min($displayRangeEnd, $numberOfPages);

        return [$displayRangeStart, $displayRangeEnd];
    }

    /**
     * Slice an iterable for the current pagination window
     *
     * @param array|object $iterable
     * @param int $itemsPerPage
     * @param int $offset
     * @return array|object
     * @throws \InvalidArgumentException
     */
    protected static function sliceIterable($iterable, $itemsPerPage, $offset)
    {
        if ($iterable instanceof QueryResultInterface) {
            $query = $iterable->getQuery()->setLimit($itemsPerPage)->setOffset($offset);

            return $query->execute();
        }

        if ($iterable instanceof ObjectStorage) {
            $iterable = $iterable->toArray();
        }

        return array_slice($iterable, $offset, $itemsPerPage);
    }

    /**
     * Assert that a given iterable is supported
     *
     * @param mixed $iterable
     * @return void
     */
    private static function assertIterable($iterable)
    {
        if (is_array($iterable) || $iterable instanceof QueryResultInterface || $iterable instanceof ObjectStorage) {
            return;
        }

        throw new \InvalidArgumentException(sprintf(
            'Unsupported iterable type "%s", expected one of %s',
            is_object($iterable) ? get_class($iterable) : gettype($iterable),
            implode(', ', [QueryResultInterface::class, ObjectStorage::class, 'array'])
        ), 1516182434);
    }
}
