<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class InvalidPatchPathException extends \InvalidArgumentException implements FastJsonPatchException {}
