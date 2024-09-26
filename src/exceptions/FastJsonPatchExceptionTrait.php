<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

trait FastJsonPatchExceptionTrait
{
    private ?string $pointer;

    private function storeContextData(?string $pointer): void
    {
        $this->pointer = $pointer;
    }

    public function getContextPointer(): ?string
    {
        return $this->pointer;
    }
}
