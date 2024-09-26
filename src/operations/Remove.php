<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Remove extends PatchOperation
{
    public function validate(object $patch): void
    {
        $this->assertValidOp($patch);
        $this->assertValidPath($patch);
    }

    public function apply(mixed &$document, object $patch): mixed
    {
        return $this->documentRemover($document, $patch->path);
    }
}
