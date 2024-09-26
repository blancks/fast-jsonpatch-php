<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\exceptions\MalformedPathException;

trait PatchValidationTrait
{
    protected function assertValidOp(object $patch): void
    {
        if (!isset($patch->op)) {
            throw new InvalidPatchOperationException('"op" is missing');
        }
    }

    protected function assertValidPath(object $patch): void
    {
        if (!isset($patch->path)) {
            throw new InvalidPatchOperationException('"path" is missing');
        }

        $this->assertValidJsonPointer($patch->path);
    }

    protected function assertValidFrom(object $patch): void
    {
        if (!isset($patch->from)) {
            throw new InvalidPatchOperationException('"from" is missing');
        }

        $this->assertValidJsonPointer($patch->from);
    }

    protected function assertValidValue(object $patch): void
    {
        if (!property_exists($patch, 'value')) {
            throw new InvalidPatchOperationException('"value" is missing');
        }
    }

    protected function assertValidJsonPointer(string $pointer): void
    {
        if ($pointer !== '' && !str_starts_with($pointer, '/')) {
            throw new MalformedPathException(sprintf('path "%s" is missing a leading slash', $pointer), $pointer);
        }
    }
}
