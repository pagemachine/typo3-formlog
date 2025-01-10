<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Updates;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

final class FormLogEntryPageUpdate implements UpgradeWizardInterface
{
    public function __construct(
        private readonly ConnectionPool $connectionPool
    ) {}

    public function getIdentifier(): string
    {
        return self::class;
    }

    public function getTitle(): string
    {
        return 'Form Log Entry Page Update';
    }

    public function getDescription(): string
    {
        return 'Move page info of form log entries to a dedicated field';
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_formlog_entries');

        $affectedRowCount = $queryBuilder
            ->update('tx_formlog_entries')
            ->set('page', $queryBuilder->quoteIdentifier('pid'), false)
            ->set('pid', 0)
            ->where($queryBuilder->expr()->eq('page', 0))
            ->executeStatement();

        return $affectedRowCount > 0;
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_formlog_entries');

        $entriesWithoutPageCount = $queryBuilder
            ->count('*')
            ->from('tx_formlog_entries')
            ->where($queryBuilder->expr()->eq('page', 0))
            ->executeQuery()
            ->fetchOne();

        return $entriesWithoutPageCount > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
