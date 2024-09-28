<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\exceptions\InvalidPatchFromException;
use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\exceptions\InvalidPatchPathException;
use blancks\JsonPatch\exceptions\InvalidPatchValueException;
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
            throw new InvalidPatchPathException('"path" is missing');
        }

        $this->assertValidJsonPointer($patch->path);
    }

    protected function assertValidFrom(object $patch): void
    {
        if (!isset($patch->from)) {
            throw new InvalidPatchFromException('"from" is missing');
        }

        $this->assertValidJsonPointer($patch->from);
    }

    protected function assertValidValue(object $patch): void
    {
        if (!property_exists($patch, 'value')) {
            throw new InvalidPatchValueException('"value" is missing');
        }
    }

    protected function assertValidJsonPointer(string $pointer): void
    {
        if ($pointer !== '' && !str_starts_with($pointer, '/')) {
            throw new MalformedPathException(sprintf('path "%s" is missing a leading slash', $pointer), $pointer);
        }
    }
}
