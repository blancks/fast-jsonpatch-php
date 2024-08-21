<?php declare(strict_types=1);

namespace blancks\JsonPatch\exceptions;

class ArrayBoundaryException extends \OutOfBoundsException implements FastJsonPatchException {}
