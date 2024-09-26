<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Move extends PatchOperation
{
    public function validate(object $patch): void
    {
        $this->assertValidOp($patch);
        $this->assertValidPath($patch);
        $this->assertValidFrom($patch);
    }

    public function apply(mixed &$document, object $patch): mixed
    {
        $value = $this->documentRemover($document, $patch->from);
        return $this->documentWriter($document, $patch->path, $value);
    }
}
