<?php
declare(strict_types = 1);

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
    private ConnectionPool $connectionPool;

    public function __construct(ConnectionPool $connectionPool)
    {
        $this->connectionPool = $connectionPool;
    }

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

        $rows = $queryBuilder
            ->execute()
            ->fetchAll(\PDO::FETCH_NUM);
        $suggestions = array_column($rows, 0);

        return $suggestions;
    }
}
