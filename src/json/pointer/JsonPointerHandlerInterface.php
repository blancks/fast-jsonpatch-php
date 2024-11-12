<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\pointer;

interface JsonPointerHandlerInterface
{
    public function isValidPointer(string $pointer): bool;

    /**
     * Should return the given JSON Pointer as an array of tokens
     * @param string $pointer the JSON Pointer
     * @return string[]
     */
    public function getTokensFromPointer(string $pointer): array;
}
