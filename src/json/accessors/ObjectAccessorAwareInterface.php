<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

interface ObjectAccessorAwareInterface
{
    public function setObjectAccessor(ObjectAccessorInterface $ObjectAccessor): void;
}
