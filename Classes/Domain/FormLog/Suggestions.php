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
        $this->query = $query ?: GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_formlog_entries');
    }
    /**
     * Get suggestions for a form log property
     *
     * @param string $property the form log property, may use "nested.notation"
     * @return array
     */
    public function getForProperty(string $property): array
    {
        $suggestions = [];

        if ($property === 'page.title') {
            $rows = $this->query
                ->select('pages.title')
                ->from('tx_formlog_entries')
                ->join(
                    'tx_formlog_entries',
                    'pages',
                    'pages',
                    $this->query->expr()->eq('pages.uid', $this->query->quoteIdentifier('tx_formlog_entries.pid'))
                )
                ->groupBy('pages.title')
                ->execute()
                ->fetchAll();
            $suggestions = array_column($rows, 'title');
        }

        return $suggestions;
    }
}
