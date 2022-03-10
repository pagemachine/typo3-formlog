<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Form log',
    'description' => 'Form log for TYPO3',
    'category' => 'misc',
    'author' => 'Mathias Brodala',
    'author_email' => 'mbrodala@pagemachine.de',
    'author_company' => 'Pagemachine AG',
    'state' => 'stable',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.99.99',
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
];
