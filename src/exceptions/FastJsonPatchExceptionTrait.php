<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

trait FastJsonPatchExceptionTrait
{
    private ?string $pointer;
    private ?string $document;

    private function storeContextData(?string $pointer, ?string $document): void
    {
        $this->pointer = $pointer;
        $this->document = $document;
    }

    public function getContextPointer(): ?string
    {
        return $this->pointer;
    }

    public function getContextDocument(): ?string
    {
        return $this->document;
    }
}
