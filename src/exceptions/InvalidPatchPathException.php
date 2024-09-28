<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class InvalidPatchPathException extends \InvalidArgumentException implements FastJsonPatchValidationException
{
    use FastJsonPatchExceptionTrait;

    public function __construct(string $message, ?string $pointer = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->storeContextData($pointer);
    }
}
