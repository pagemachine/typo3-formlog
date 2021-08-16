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
        'readOnly' => true,
        'iconfile' => 'EXT:formlog/Resources/Public/Icons/tx_formlog_entries.svg',
    ],
    'interface' => [
        'showRecordFieldList' => 'crdate, language, identifier, data',
    ],
    'types' => [
        '0' => [
            'showitem' => 'crdate, language, identifier, data, finisher_variables',
        ],
    ],
    'columns' => [
        'pid' => [
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'pages',
            ],
        ],
        'crdate' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.creationDate',
            'config' => [
                'type' => 'none',
                'format' => 'datetime',
                'eval' => 'datetime',
            ],
        ],
        'language' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:formlog/Resources/Private/Language/locallang_db.xml:tx_formlog_entries.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'items' => [
                    ['LLL:EXT:formlog/Resources/Private/Language/locallang_db.xml:tx_formlog_entries.language.default', '0'],
                ],
                'allowNonIdValues' => true,
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
