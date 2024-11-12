<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\handlers;

use blancks\JsonPatch\json\crud\CrudInterface;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerInterface;

interface JsonHandlerInterface extends CrudInterface, JsonPointerHandlerInterface
{
    /**
     * @param mixed $document
     * @param array<string, mixed> $options
     * @return string
     */
    public function encode(mixed $document, array $options = []): string;

    /**
     * @param string $json
     * @param array<string, mixed> $options
     * @return mixed
     */
    public function decode(string $json, array $options = []): mixed;
}
