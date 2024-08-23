<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

interface FastJsonPatchException extends \Throwable
{
    public function getContextPointer(): ?string;
    public function getContextDocument(): ?string;
}
