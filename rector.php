<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Ssch\TYPO3Rector\TYPO313\v0\MigrateTypoScriptFrontendControllerReadOnlyPropertiesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration/TCA',
        __DIR__ . '/Tests',
    ])
    ->withRootFiles()
    ->withImportNames(
        importShortClasses: false,
        removeUnusedImports: true,
    )
    ->withPhpSets()
    ->withSets([
        PHPUnitSetList::PHPUNIT_100,
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ])
    ->withSkip([
        ...(
            class_exists(MigrateTypoScriptFrontendControllerReadOnlyPropertiesRector::class) ? [
                MigrateTypoScriptFrontendControllerReadOnlyPropertiesRector::class => [
                    __DIR__ . '/Classes/Domain/Form/Finishers/LoggerFinisher.php',
                ],
            ] : []
        ),
    ])
;
