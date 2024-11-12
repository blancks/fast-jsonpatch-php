<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\pointer;

interface JsonPointerHandlerAwareInterface
{
    public function setJsonPointerHandler(JsonPointerHandlerInterface $jsonPointerHandler): void;
}
