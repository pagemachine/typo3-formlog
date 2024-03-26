<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\Data;

use Pagemachine\Formlog\Domain\Data\JsonData;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Domain\Data\JsonData
 */
final class JsonDataTest extends UnitTestCase
{
    #[Test]
    public function parseJsonString(): JsonData
    {
        $jsonString = '{"foo":"bar","qux":10}';
        $data = new JsonData($jsonString);

        self::assertEquals('bar', $data['foo']);
        self::assertEquals(10, $data['qux']);

        return $data;
    }

    #[Depends('parseJsonString')]
    public function formatJsonData(JsonData $data): void
    {
        $jsonString = (string)$data;

        self::assertEquals('{"foo":"bar","qux":10}', $jsonString);
    }
}
