<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog\Tests\Unit;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use Pagemachine\Formlog\Json;

/**
 * Testcase for Pagemachine\Formlog\Json
 */
final class JsonTest extends UnitTestCase
{
    /**
     * @test
     */
    public function failDecodingInvalidJsonString(): void
    {
        $this->expectException(\JsonException::class);

        Json::decode('');
    }

    /**
     * @test
     */
    public function failEncodingRecursiveValue(): void
    {
        $array1 = [];
        $array2 = [];
        $array1[0] = &$array2;
        $array2[0] = &$array1;

        $this->expectException(\JsonException::class);

        Json::encode($array1);
    }
}
