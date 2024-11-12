<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\pointer;

trait JsonPointerHandlerAwareTrait
{
    protected JsonPointerHandlerInterface $jsonPointerHandler;

    public function setJsonPointerHandler(JsonPointerHandlerInterface $jsonPointerHandler): void
    {
        $this->jsonPointerHandler = $jsonPointerHandler;
    }
}
