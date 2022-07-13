<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Domain\Repository;

/*
 * This file is part of the Pagemachine TYPO3 Formlog project.
 */

use Pagemachine\Formlog\Domain\FormLog\DateRangeFilter;
use Pagemachine\Formlog\Domain\FormLog\Filters;
use Pagemachine\Formlog\Domain\FormLog\ValueFilter;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for form log entries
 */
class FormLogEntryRepository extends Repository
{
    protected $defaultOrderings = [
        'submissionDate' => QueryInterface::ORDER_DESCENDING,
    ];

    /**
     * @return QueryInterface
     */
    public function createQuery()
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query;
    }

    /**
     * Find all objects optionally filtered
     */
    public function findAllFiltered(Filters $filters): QueryResultInterface
    {
        $query = $this->createQuery();
        $constraints = [
            // Dummy constraint to satisfy logicalAnd() requirement
            $query->greaterThan('uid', 0),
        ];

        foreach ($filters as $propertyName => $filter) {
            if ($filter->isEmpty()) {
                continue;
            }

            if ($filter instanceof ValueFilter) {
                $constraints[] = $query->equals($propertyName, $filter->getValue());
            } elseif ($filter instanceof DateRangeFilter) {
                $constraints[] = $query->logicalAnd([
                    $query->greaterThanOrEqual($propertyName, $filter->getStartDate()),
                    $query->lessThanOrEqual($propertyName, $filter->getEndDate()),
                ]);
            }
        }

        $query->matching($query->logicalAnd($constraints));

        return $query->execute();
    }
}
