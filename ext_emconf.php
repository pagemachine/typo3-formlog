<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form log',
    'description' => 'Form log for TYPO3',
    'category' => 'misc',
    'author' => 'Mathias Brodala',
    'author_email' => 'mbrodala@pagemachine.de',
    'author_company' => 'Pagemachine AG',
    'state' => 'stable',
    'version' => '1.4.1',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-7.99.99',
            'typo3' => '8.7.0-10.4.99',
        ],
    ],
];
