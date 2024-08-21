<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class InvalidPatchValueException extends \InvalidArgumentException implements FastJsonPatchException {}
