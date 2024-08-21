<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class UnknownPatchOperationException extends \LogicException implements FastJsonPatchException {}
