<?php

declare(strict_types=1);

namespace Pagemachine\Formlog\Tests\Unit;

use Pagemachine\Formlog\Json;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Testcase for Pagemachine\Formlog\Json
 */
final class JsonTest extends UnitTestCase
{
    #[Test]
    public function failDecodingInvalidJsonString(): void
    {
        $this->expectException(\JsonException::class);

        Json::decode('');
    }

    #[Test]
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
