<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
        PHPUnitSetList::PHPUNIT_91,
        Typo3SetList::TYPO3_11,
    ]);

    $rectorConfig->skip([
        \Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector::class,
    ]);
};
