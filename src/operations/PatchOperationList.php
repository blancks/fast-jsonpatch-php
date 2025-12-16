<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\handlers\JsonHandlerInterface;
use ArgumentCountError;
use Error;
use stdClass;
use TypeError;

final readonly class PatchOperationList implements \JsonSerializable
{
    /**
     * @phpstan-var list<PatchOperation>
     */
    public array $operations;

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
                function (mixed $patch) use ($classes) {
                    [$op, $values] = self::extractPatchOpAndValues($patch);
                    if (!isset($classes[$op])) {
                        throw new InvalidPatchOperationException(sprintf('Unknown operation "%s"', $op));
                    }
                    return self::createPatchDtoFromValues($op, $classes[$op], $values);
                },
                $patches
            ),
        );
    }

    /**
     * @phpstan-return array{string, array<string, mixed>}
     */
    private static function extractPatchOpAndValues(mixed $patch): array
    {
        if (!$patch instanceof stdClass) {
            throw new InvalidPatchOperationException(
                sprintf('Each patch item must be an object, got %s', get_debug_type($patch))
            );
        }

        if (!isset($patch->op)) {
            throw new InvalidPatchOperationException('Each patch item must specify "op"');
        }

        if (!is_string($patch->op)) {
            throw new InvalidPatchOperationException(
                sprintf('Patch "op" must be a string, got %s', get_debug_type($patch->op))
            );
        }

        $op = $patch->op;
        unset($patch->op);

        $values = [];
        foreach ((array) $patch as $key => $value) {
            // We rely on the properties being strings, to ensure that PHP will enforce that all named properties are
            // present / defined when creating the arbitrary DTO object. Ensure this is the case
            if (!is_string($key)) {
                throw new InvalidPatchOperationException('All patch operation properties must have string names');
            }
            $values[$key] = $value;
        }

        return [$op, $values];
    }

    /**
     * @phpstan-param class-string<PatchOperation> $class
     * @param array<string, mixed> $values
     * @return PatchOperation
     *
     * @throws InvalidPatchOperationException
     */
    private static function createPatchDtoFromValues(string $op, string $class, array $values): PatchOperation
    {
        try {
            return new $class(...$values);
        } catch (ArgumentCountError $e) {
            // We can be relatively confident that this was triggered for the DTO constructor. If the DTO was calling a
            // method (e.g. a parent method / internal helper method) with incorrect argument counts that should have
            // been detected by its own tests.
            throw new InvalidPatchOperationException(
                sprintf('Missing required param(s) for %s operation as %s: %s', $op, $class, $e->getMessage()),
                previous: $e,
            );
        } catch (TypeError $e) {
            // We can be relatively confident that this relates to the property types passed to the DTO constructor.
            // If the DTO constructor has a type signature that does not match the expected supported values, that
            // should have been detected by its own tests.
            throw new InvalidPatchOperationException(
                sprintf('Invalid param(s) for %s operation as %s: %s', $op, $class, $e->getMessage()),
                previous: $e,
            );
        } catch (Error $e) {
            if (str_contains($e->getMessage(), 'Unknown named parameter')) {
                // This does not have a dedicated error type so we have to match on the message.
                throw new InvalidPatchOperationException(
                    sprintf('Unexpected param(s) for %s operation as %s: %s', $op, $class, $e->getMessage()),
                    previous: $e,
                );
            }
            throw $e;
        }
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
