<?php declare(strict_types=1);

namespace blancks\JsonPatch\accessors;

interface ArrayAccessorAwareInterface
{
    public function setArrayAccessor(ArrayAccessorInterface $ArrayAccessor): void;
}
