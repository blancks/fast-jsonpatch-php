<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\handlers;

interface JsonHandlerAwareInterface
{
    public function setJsonHandler(JsonHandlerInterface $JsonHandler): void;
}
