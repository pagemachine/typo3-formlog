<?php

use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create();
$config->getFinder()
    ->in(__DIR__)
    ->exclude([
        'var',
        'web',
    ]);

return $config;
