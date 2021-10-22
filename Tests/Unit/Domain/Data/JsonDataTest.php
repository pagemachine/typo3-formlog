<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit\Domain\Data;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Domain\Data\JsonData;

/**
 * Testcase for Pagemachine\Formlog\Domain\Data\JsonData
 */
final class JsonDataTest extends UnitTestCase
{
    /**
     * @test
     */
    public function parseJsonString(): JsonData
    {
        $jsonString = '{"foo":"bar","qux":10}';
        $data = new JsonData($jsonString);

        $this->assertEquals('bar', $data['foo']);
        $this->assertEquals(10, $data['qux']);

        return $data;
    }

    /**
     * @depends parseJsonString
     */
    public function formatJsonData(JsonData $data): void
    {
        $jsonString = (string)$data;

        $this->assertEquals('{"foo":"bar","qux":10}', $jsonString);
    }

    /**
     * @test
     */
    public function failOnInvalidJsonString(): void
    {
        $this->expectException(\JsonException::class);

        new JsonData('');
    }
}
