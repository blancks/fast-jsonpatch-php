<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\crud;

interface CrudInterface
{
    public function write(mixed &$document, string $path, mixed $value): mixed;
    public function delete(mixed &$document, string $path): mixed;
    public function &read(mixed &$document, string $path): mixed;
}
