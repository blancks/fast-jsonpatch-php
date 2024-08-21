<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class UnknownPathException extends \DomainException implements FastJsonPatchException {}
