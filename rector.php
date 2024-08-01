<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests',
    ])
    ->withRootFiles()
    ->withPhpSets()
    ->withSets([
        PHPUnitSetList::PHPUNIT_100,
        Typo3SetList::TYPO3_11,
    ])
    ->withSkip([
        \Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class,
    ])
;
