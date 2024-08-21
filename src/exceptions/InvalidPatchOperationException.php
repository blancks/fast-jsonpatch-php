<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class InvalidPatchOperationException extends \InvalidArgumentException implements FastJsonPatchException {}
