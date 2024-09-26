<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Add extends PatchOperation
{
    public function validate(object $patch): void
    {
        $this->assertValidOp($patch);
        $this->assertValidPath($patch);
        $this->assertValidValue($patch);
    }

    public function apply(mixed &$document, object $patch): mixed
    {
        return $this->documentWriter($document, $patch->path, $patch->value);
    }
}
