<?php

return [
    \Pagemachine\Formlog\Domain\Model\FormLogEntry::class => [
        'tableName' => 'tx_formlog_entries',
        'properties' => [
            'submissionDate' => [
                'fieldName' => 'crdate',
            ],
            'page' => [
                'fieldName' => 'pid',
            ],
        ],
    ],
    \Pagemachine\Formlog\Domain\Model\FormLogEntry\Page::class => [
        'tableName' => 'pages',
    ],
];
