<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class MalformedPathException extends \DomainException implements FastJsonPatchException {}
