<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class MalformedPathException extends \DomainException implements FastJsonPatchException
{
    use FastJsonPatchExceptionTrait;

    public function __construct(string $message, ?string $pointer = null, ?string $document = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->storeContextData($pointer, $document);
    }
}
