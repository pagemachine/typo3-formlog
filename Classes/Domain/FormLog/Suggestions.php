<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Provider for form log suggestions
 */
final class Suggestions
{
    public function __construct(private readonly ConnectionPool $connectionPool) {}

    /**
     * Get suggestions for a form log property
     *
     * @param string $property the form log property, may use "nested.notation"
     */
    public function getForProperty(string $property): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_formlog_entries');
        $queryBuilder
            ->select($property)
            ->from('tx_formlog_entries')
            ->join(
                'tx_formlog_entries',
                'pages',
                'page',
                $queryBuilder->expr()->eq('page.uid', $queryBuilder->quoteIdentifier('tx_formlog_entries.pid'))
            )
            ->orderBy($property)
            ->groupBy($property);

        $result = $queryBuilder->executeQuery();
        $suggestions = $result->fetchFirstColumn();

        return $suggestions;
    }
}
