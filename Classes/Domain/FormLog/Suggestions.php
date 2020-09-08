<?php
declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\FormLog;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provider for form log suggestions
 */
class Suggestions
{
    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @param QueryBuilder|null $query
     */
    public function __construct(QueryBuilder $query = null)
    {
        $this->query = $query ?: $this->getConnectionPool()->getQueryBuilderForTable('tx_formlog_entries');
    }

    /**
     * Get suggestions for a form log property
     *
     * @param string $property the form log property, may use "nested.notation"
     * @return array
     */
    public function getForProperty(string $property): array
    {
        $rows = $this->query
            ->select($property)
            ->from('tx_formlog_entries')
            ->join(
                'tx_formlog_entries',
                'pages',
                'page',
                $this->query->expr()->eq('page.uid', $this->query->quoteIdentifier('tx_formlog_entries.pid'))
            )
            ->orderBy($property)
            ->groupBy($property)
            ->execute()
            ->fetchAll(\PDO::FETCH_NUM);
        $suggestions = array_column($rows, 0);

        return $suggestions;
    }

    private function getConnectionPool(): ConnectionPool
    {
        /** @var ConnectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $connectionPool;
    }
}
