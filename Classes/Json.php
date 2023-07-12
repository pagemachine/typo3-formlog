<?php

declare(strict_types = 1);

namespace Pagemachine\Formlog;

final class Json
{
    /**
     * @throws \JsonException if decoding fails
     */
    public static function decode(string $jsonString): array
    {
        $decoded = json_decode(
            $jsonString === '' ? '[]' : $jsonString,
            true,
            512,
            \JSON_THROW_ON_ERROR
        );

        return $decoded;
    }

    /**
     * @param mixed $value
     *
     * @throws \JsonException if encoding fails
     */
    public static function encode($value): string
    {
        $encoded = json_encode(
            $value,
            \JSON_THROW_ON_ERROR,
            512
        );

        return $encoded;
    }

    private function __construct()
    {
    }
}
