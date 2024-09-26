<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Replace extends PatchOperation
{
    public function validate(object $patch): void
    {
        $this->assertValidOp($patch);
        $this->assertValidPath($patch);
        $this->assertValidValue($patch);
    }

    public function apply(mixed &$document, object $patch): mixed
    {
        $previous = $this->documentRemover($document, $patch->path);
        $this->documentWriter($document, $patch->path, $patch->value);
        return $previous;
    }
}
