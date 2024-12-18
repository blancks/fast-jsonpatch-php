<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\pointer;

trait JsonPointerHandlerAwareTrait
{
    protected JsonPointerHandlerInterface $JsonPointerHandler;

    public function setJsonPointerHandler(JsonPointerHandlerInterface $JsonPointerHandler): void
    {
        $this->JsonPointerHandler = $JsonPointerHandler;
    }
}
