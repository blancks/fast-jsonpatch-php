<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

interface ValueAccessorAwareInterface
{
    public function setValueAccessor(ValueAccessorInterface $ValueAccessor): void;
}
