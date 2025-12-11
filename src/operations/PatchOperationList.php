<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\handlers\JsonHandlerInterface;
use stdClass;

final class PatchOperationList implements \JsonSerializable
{
    /**
     * @phpstan-var list<PatchOperation>
     */
    public readonly array $operations;

    /**
     * @param string $jsonOperations
     * @param JsonHandlerInterface $jsonHandler
     * @param array<string, class-string<PatchOperation>> $customClasses
     * @return self
     */
    public static function fromJson(
        string $jsonOperations,
        JsonHandlerInterface $jsonHandler = new BasicJsonHandler(),
        array $customClasses = [],
    ): self {
        $patches = $jsonHandler->decode($jsonOperations);
        if (!(is_array($patches) && array_is_list($patches))) {
            throw new InvalidPatchException(
                sprintf('Invalid patch structure (expected list, got %s)', get_debug_type($patches)),
            );
        }

        $classes = [
            'add' => Add::class,
            'copy' => Copy::class,
            'move' => Move::class,
            'remove' => Remove::class,
            'replace' => Replace::class,
            'test' => Test::class,
            ...$customClasses,
        ];

        return new PatchOperationList(
            ...array_map(
                function (stdClass $patch) use ($classes) {
                    // The top-level patch entries should be identical as objects or arrays, cast to array to allow
                    // spreading the properties into the DTO constructors (which are assumed to match the JSON objects)
                    $patch = (array) $patch;
                    $op = $patch['op'];
                    unset($patch['op']);
                    if (!isset($classes[$op])) {
                        throw new InvalidPatchOperationException(sprintf('Unknown operation "%s"', $op));
                    }

                    return new $classes[$op](...$patch);
                },
                $patches
            ),
        );
    }

    /**
     * @no-named-arguments
     */
    public function __construct(
        PatchOperation ...$operations
    ) {
        $this->operations = $operations;
    }

    /**
     * @return list<PatchOperation>
     */
    public function jsonSerialize(): array
    {
        return $this->operations;
    }
}
