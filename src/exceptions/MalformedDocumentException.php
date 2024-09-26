<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class MalformedDocumentException extends \UnexpectedValueException implements FastJsonPatchException
{
    use FastJsonPatchExceptionTrait;

    public function __construct(string $message, ?string $pointer = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->storeContextData($pointer);
    }
}
