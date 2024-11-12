<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\pointer;

/**
 * Handles JSON Pointer as per RFC-6901
 * @link https://datatracker.ietf.org/doc/html/rfc6901
 */
class JsonPointer6901 implements JsonPointerHandlerInterface
{
    /**
     * Tells if the pointer is valid
     * @param string $pointer
     * @return bool
     */
    public function isValidPointer(string $pointer): bool
    {
        return $pointer === '' || $pointer[0] === '/';
    }

    /**
     * Returns the token list of the given $pointer
     * @param string $pointer the JSON Pointer
     * @return string[]
     */
    public function getTokensFromPointer(string $pointer): array
    {
        if ($pointer !== '') {
            $pointer = strtr(substr($pointer, 1), ['/' => '~ ', '~1' => '/', '~0' => '~']);
            return explode('~ ', $pointer);
        }

        return [];
    }
}
