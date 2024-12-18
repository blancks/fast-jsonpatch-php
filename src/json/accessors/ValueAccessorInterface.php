<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

interface ValueAccessorInterface
{
    public function write(
        ArrayAccessorInterface|ObjectAccessorInterface $Accessor,
        mixed &$document,
        string $path,
        ?string $token,
        mixed $value
    ): mixed;

    public function &read(
        ArrayAccessorInterface|ObjectAccessorInterface $Accessor,
        mixed &$document,
        string $path,
        ?string $token
    ): mixed;

    public function delete(
        ArrayAccessorInterface|ObjectAccessorInterface $Accessor,
        mixed &$document,
        string $path,
        ?string $token
    ): mixed;
}
