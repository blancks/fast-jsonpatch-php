<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

interface ArrayAccessorAwareInterface
{
    public function setArrayAccessor(ArrayAccessorInterface $ArrayAccessor): void;
}
