<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class InvalidPatchFromException extends \InvalidArgumentException implements FastJsonPatchException {}
