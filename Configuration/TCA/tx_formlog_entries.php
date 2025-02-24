<?php

defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_db.xlf:tx_formlog_entries',
        'label' => 'uid',
        'default_sortby' => 'crdate DESC',
        'crdate' => 'crdate',
        'tstamp' => 'tstamp',
        'delete' => 'deleted',
        'rootLevel' => 1,
        'readOnly' => true,
        'iconfile' => 'EXT:formlog/Resources/Public/Icons/tx_formlog_entries.svg',
    ],
    'types' => [
        '0' => [
            'showitem' => 'crdate, page, language, identifier, data, finisher_variables',
        ],
    ],
    'columns' => [
        'crdate' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.creationDate',
            'config' => [
                'type' => 'none',
                'format' => 'datetime',
                'eval' => 'datetime',
            ],
        ],
        'page' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_db.xml:tx_formlog_entries.page',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
                'foreign_table' => 'pages',
                'maxitems' => 1,
                'size' => 1,
                'readOnly' => true,
            ],
        ],
        'language' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_db.xml:tx_formlog_entries.language',
            'config' => [
                'type' => 'language',
                'readOnly' => true,
            ],
        ],
        'identifier' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_db.xml:tx_formlog_entries.identifier',
            'config' => [
                'type' => 'none',
            ],
        ],
        'data' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_db.xml:tx_formlog_entries.data',
            'config' => [
                'type' => 'text',
                'renderType' => 'jsonData',
                'readOnly' => true,
            ],
        ],
        'finisher_variables' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_db.xml:tx_formlog_entries.finisher_variables',
            'config' => [
                'type' => 'text',
                'renderType' => 'jsonData',
                'readOnly' => true,
            ],
        ],
    ],
];
