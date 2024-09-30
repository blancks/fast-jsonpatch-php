<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\handlers;

trait JsonHandlerAwareTrait
{
    protected JsonHandlerInterface $JsonHandler;

    public function setJsonHandler(JsonHandlerInterface $JsonHandler): void
    {
        $this->JsonHandler = $JsonHandler;
    }
}
