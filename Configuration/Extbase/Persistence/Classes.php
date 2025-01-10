<?php

return [
    \Pagemachine\Formlog\Domain\Model\FormLogEntry::class => [
        'tableName' => 'tx_formlog_entries',
        'properties' => [
            'submissionDate' => [
                'fieldName' => 'crdate',
            ],
        ],
    ],
    \Pagemachine\Formlog\Domain\Model\FormLogEntry\Page::class => [
        'tableName' => 'pages',
    ],
];
