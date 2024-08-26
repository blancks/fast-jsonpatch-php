<?php declare(strict_types=1);

namespace blancks\JsonPatch;

use blancks\JsonPatch\exceptions\{
    AppendToObjectException,
    ArrayBoundaryException,
    FailedTestException,
    FastJsonPatchException,
    InvalidJsonDepthException,
    InvalidPatchException,
    InvalidPatchFromException,
    InvalidPatchOperationException,
    InvalidPatchPathException,
    InvalidPatchValueException,
    MalformedDocumentException,
    MalformedPathException,
    UnknownPathException,
    UnknownPatchOperationException
};

/**
 * This class allow to perform a sequence of operations to apply to a target JSON document as per RFC 6902
 * @link https://datatracker.ietf.org/doc/html/rfc6902
 */
final class FastJsonPatch
{
    /**
     * Applies $patch to the $json string provided and returns the updated json string
     *
     * @param string $json
     * @param string $patch
     * @param int $depth same parameter used by json_decode function
     * @param int $flags same parameter used by json_decode function. JSON_THROW_ON_ERROR is added by default
     * @return string
     * @throws FastJsonPatchException
     */
    public static function apply(string $json, string $patch, int $depth = 512, int $flags = 0): string
    {
        return self::documentToString(
            self::applyDecode($json, $patch, false, $depth),
            JSON_THROW_ON_ERROR | $flags,
            $depth
        );
    }

    /**
     * Applies $patch to the $json string provided and returns the decoded document
     *
     * @param string $json
     * @param string $patch
     * @param bool $associative same parameter used by json_decode function
     * @param int $depth same parameter used by json_decode function
     * @param int $flags same parameter used by json_decode function. JSON_THROW_ON_ERROR is added by default
     * @return array<int|string, mixed>|\stdClass
     * @throws FastJsonPatchException
     */
    public static function applyDecode(
        string $json,
        string $patch,
        bool $associative = false,
        int $depth = 512,
        int $flags = 0
    ): \stdClass|array {
        $document = self::stringToDocument($json, $associative, $depth, $flags);

        if (!is_array($document) && !($document instanceof \stdClass)) {
            throw new MalformedDocumentException(
                'Invalid JSON document, must be an array or stdClass object.',
                '',
                $json
            );
        }

        $patch = self::stringToDocument($patch, $associative, $depth, $flags);

        if (!is_array($patch)) {
            throw new InvalidPatchException('Invalid patch json, must resolve to an array of patches');
        }

        self::applyByReference($document, $patch);
        return $document;
    }

    /**
     * Applies the provided $patch list to the $document passed by reference.
     *
     * IMPORTANT: if you need to produce json strings for output purposes make sure that your
     * JSON-decoder class decodes objects as \stdClass instances instead of arrays, otherwise you
     * may get inaccurate representation in case of object with numerical index, such as {"0":1,"1":2,...}
     * because PHP can't distinguish that from [1,2,...]. If you just need to consume the document
     * this difference should not be a problem for you, unless of some specific application behavior
     * that you should already be aware of.
     *
     * @param array<int|string, mixed>|\stdClass $document decoded json document passed by reference
     * @param array<int, \stdClass> $patch decoded list of patches that must be applied to $document
     * @throws FastJsonPatchException
     */
    public static function applyByReference(array|\stdClass &$document, array $patch): void
    {
        self::validateDecodedPatch($patch);
        $optionalNodes = ['from', 'path', 'value'];

        foreach ($patch as $p) {
            $p = (array) $p;
            $parameters = [];

            foreach ($optionalNodes as $key) {
                if (array_key_exists($key, $p)) {
                    $parameters[$key] = $key === 'path' || $key === 'from'
                        ? self::pathSplitter($p[$key])
                        : $p[$key];
                }
            }

            self::{'op' . ucfirst($p['op'])}($document, ...$parameters);
        }
    }

    /**
     * Parses a $jsonpointer path against $json and returns the value
     *
     * @param string $json
     * @param string $pointer JSON Pointer
     * @return mixed
     * @throws FastJsonPatchException
     */
    public static function parsePath(string $json, string $pointer): mixed
    {
        $document = json_decode($json);
        return self::parsePathByReference($document, $pointer);
    }

    /**
     * Parses a $jsonpointer path against $json and returns the value
     *
     * @param array<int|string, mixed>|\stdClass $document
     * @param string $pointer JSON Pointer
     * @return mixed
     * @throws FastJsonPatchException
     */
    public static function parsePathByReference(array|\stdClass &$document, string $pointer): mixed
    {
        self::assertValidJsonPointer($pointer);
        return self::documentReader($document, self::pathSplitter($pointer));
    }

    /**
     * Validates the JSON Patch document structure.
     * throws a FastJsonPatchException if the $patch is invalid
     *
     * @param string $patch decoded list of patches that must be applied to $document
     * @return void
     * @throws InvalidPatchOperationException
     * @throws InvalidPatchPathException
     * @throws InvalidPatchValueException
     * @throws InvalidPatchFromException
     * @throws UnknownPatchOperationException
     * @throws MalformedPathException
     */
    public static function validatePatch(string $patch): void
    {
        /** @var array<int, \stdClass> $decoded */
        $decoded = json_decode($patch);
        self::validateDecodedPatch($decoded);
    }

    /**
     * ADD Operation
     *
     * Performs one of the following functions, depending upon what the target location references:
     *  * If the target location specifies an array index, a new value is inserted into the array at the specified index.
     *  * If the target location specifies an object member that does not already exist, a new member is added to the object.
     *  * If the target location specifies an object member that does exist, that member's value is replaced.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.1
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $path
     * @param mixed $value
     * @return void
     */
    private static function opAdd(array|\stdClass &$document, array $path, mixed $value): void
    {
        self::documentWriter($document, $path, $value);
    }

    /**
     * REMOVE Operation
     * Removes the value at the target location. The target location MUST exist for the operation to be successful.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.2
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $path
     * @return void
     */
    private static function opRemove(array|\stdClass &$document, array $path): void
    {
        self::documentRemover($document, $path);
    }

    /**
     * REPLACE Operation
     * Replaces the value at the target location with a new value.
     * The target location MUST exist for the operation to be successful.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.3
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $path
     * @param mixed $value
     * @return void
     */
    private static function opReplace(array|\stdClass &$document, array $path, mixed $value): void
    {
        self::documentRemover($document, $path);
        self::documentWriter($document, $path, $value);
    }

    /**
     * MOVE Operation
     * Removes the value at a specified location and adds it to the target location.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.4
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $from
     * @param string[] $path
     * @return void
     */
    private static function opMove(array|\stdClass &$document, array $from, array $path): void
    {
        $value = self::documentRemover($document, $from);
        self::documentWriter($document, $path, $value);
    }

    /**
     * COPY Operation
     * Copies the value at a specified location to the target location.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.5
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $from
     * @param string[] $path
     * @return void
     */
    private static function opCopy(array|\stdClass &$document, array $from, array $path): void
    {
        $value = self::documentReader($document, $from);
        self::documentWriter($document, $path, $value);
    }

    /**
     * TEST Operation
     * Tests that a value at the target location is equal to a specified value
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.6
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $path
     * @param mixed $value
     * @return void
     */
    private static function opTest(array|\stdClass &$document, array $path, mixed $value): void
    {
        $item = self::documentReader($document, $path);

        if (!self::isJsonEquals($item, $value)) {
            $valuejson = self::documentToString($value);
            throw new FailedTestException(
                sprintf('Test operation failed asserting that %s equals %s', self::documentToString($item), $valuejson),
                self::pathToString($path),
                $valuejson
            );
        }
    }

    /**
     * Adds $value at the $path location in the $document
     *
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $path
     * @param mixed $value
     * @param string[]|null $originalpath
     * @return void
     */
    private static function documentWriter(
        array|\stdClass &$document,
        array $path,
        mixed $value,
        ?array $originalpath = null
    ): void {
        if (count($path) === 0) {
            $document = $value;
            return;
        }

        $originalpath ??= $path;
        $node = array_shift($path);
        $pathLength = count($path);
        $isObject = is_object($document);

        if ($pathLength > 0) {
            self::assertPropertyExists($document, $node, $originalpath);
        }

        if ($pathLength === 0) {
            $appendToArray = $node === '-';
            $isAssociative = !$isObject && !array_is_list($document);

            if ($appendToArray && ($isObject || $isAssociative)) {
                throw new AppendToObjectException(
                    'Appending value ("-" symbol) against an object is not allowed',
                    self::pathToString($originalpath),
                    self::documentToString($document)
                );
            }

            if ($isObject) {
                $document->{$node} = $value;
                return;
            }

            /** @phpstan-ignore-next-line */
            $documentLength = count($document);
            $node = $appendToArray ? (string) $documentLength : $node;

            if ((!empty($document) && $isAssociative) || empty($document)) {
                $document[$node] = $value;
                return;
            }

            if (!is_numeric($node)) {
                throw new UnknownPathException(
                    sprintf('Invalid array index "%s"', $node),
                    self::pathToString($originalpath),
                    self::documentToString($document)
                );
            }

            $nodeInt = (int) $node;

            if ((string) $nodeInt !== $node || $nodeInt < 0 || $nodeInt > $documentLength) {
                throw new ArrayBoundaryException(
                    sprintf('Exceeding array boundaries trying to add index "%s"', $node),
                    self::pathToString($originalpath),
                    self::documentToString($document)
                );
            }

            array_splice($document, $nodeInt, 0, is_array($value) || is_object($value) ? [$value] : $value);
            return;
        }

        if ($isObject) {
            self::documentWriter($document->{$node}, $path, $value, $originalpath);
            return;
        }

        self::documentWriter($document[$node], $path, $value, $originalpath);
    }

    /**
     * Removes the value at the provided $path in the $document
     *
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $path
     * @param string[]|null $originalpath
     * @return mixed the value removed from the document
     */
    private static function documentRemover(array|\stdClass &$document, array $path, ?array $originalpath = null): mixed
    {
        if (count($path) === 0) {
            return null;
        }

        $originalpath ??= $path;
        $node = array_shift($path);
        $isObject = is_object($document);
        self::assertPropertyExists($document, $node, $originalpath);

        if (count($path) === 0) {
            $isAssociative = !$isObject && !array_is_list($document);

            if ($isObject) {
                $value = $document->{$node};
                unset($document->{$node});
                return $value;
            } elseif ($isAssociative) {
                $value = $document[$node];
                unset($document[$node]);
                return $value;
            }

            $value = $document[$node];
            array_splice($document, (int) $node, 1);
            return $value;
        }

        if ($isObject) {
            return self::documentRemover($document->{$node}, $path, $originalpath);
        }

        return self::documentRemover($document[$node], $path, $originalpath);
    }

    /**
     * Finds and returns the value at the provided $path in the $document
     *
     * @param array<int|string, mixed>|\stdClass $document
     * @param string[] $path
     * @param string[]|null $originalpath
     * @return mixed
     */
    private static function documentReader(array|\stdClass &$document, array $path, ?array $originalpath = null): mixed
    {
        if (count($path) === 0) {
            return $document;
        }

        $originalpath ??= $path;
        $node = array_shift($path);
        $isObject = is_object($document);
        self::assertPropertyExists($document, $node, $originalpath);

        if ($isObject) {
            return count($path) === 0
                ? $document->{$node}
                : self::documentReader($document->{$node}, $path, $originalpath);
        }

        return count($path) === 0
            ? $document[$node]
            : self::documentReader($document[$node], $path, $originalpath);
    }

    /**
     * Validates the JSON Patch document structure.
     * throws a FastJsonPatchException if the $patch is invalid
     *
     * @param array<int, \stdClass> $patch decoded list of patches that must be applied to $document
     * @return void
     * @throws InvalidPatchOperationException
     * @throws InvalidPatchPathException
     * @throws InvalidPatchValueException
     * @throws InvalidPatchFromException
     * @throws UnknownPatchOperationException
     * @throws MalformedPathException
     */
    private static function validateDecodedPatch(array $patch): void
    {
        foreach ($patch as $i => $p) {
            $p = (array) $p;

            if (!isset($p['op'])) {
                throw new InvalidPatchOperationException(
                    sprintf('"op" is missing in patch with index %d', $i),
                    "/{$i}",
                    self::documentToString($p)
                );
            }

            if (!isset($p['path'])) {
                throw new InvalidPatchPathException(
                    sprintf('"path" is missing in patch with index %d', $i),
                    "/{$i}",
                    self::documentToString($p)
                );
            }

            self::assertValidJsonPointer($p['path']);

            switch ($p['op']) {
                case 'add':
                case 'replace':
                case 'test':
                    if (!array_key_exists('value', $p)) {
                        throw new InvalidPatchValueException(
                            sprintf('"value" is missing in patch with index %d', $i),
                            "/{$i}",
                            self::documentToString($p)
                        );
                    }
                    break;
                case 'copy':
                case 'move':
                    if (!isset($p['from'])) {
                        throw new InvalidPatchFromException(
                            sprintf('"from" is missing in patch with index %d', $i),
                            "/{$i}",
                            self::documentToString($p)
                        );
                    }

                    self::assertValidJsonPointer($p['from']);
                    break;
                case 'remove':
                    break;  // only needs "op" and "path" as mandatory properties
                default:
                    throw new UnknownPatchOperationException(
                        sprintf('Unknown operation "%s" in patch with index %d', $p['op'], $i),
                        "/{$i}",
                        self::documentToString($p)
                    );
            }
        }
    }

    /**
     * Returns the $path tokens as array
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6901#section-3
     * @param string $path
     * @return string[]
     * @throws MalformedPathException
     */
    private static function pathSplitter(string $path): array
    {
        return $path === '' ? [] : array_map(
            fn(string $part): string => strtr($part, ['~1' => '/', '~0' => '~']),
            explode('/', ltrim($path, '/'))
        );
    }

    /**
     * Converts a JSON Pointer back to its string state
     *
     * @param string[] $path
     * @return string
     */
    private static function pathToString(array $path): string
    {
        return count($path) === 0 ? '' : '/' . implode('/', $path);
    }

    /**
     * Decodes the JSON string
     *
     * @param string $json
     * @param bool $associative
     * @param int $depth
     * @param int $flags
     * @return mixed
     */
    private static function stringToDocument(
        string $json,
        bool $associative = false,
        int $depth = 512,
        int $flags = 0
    ): mixed {
        if ($depth < 1) {
            throw new InvalidJsonDepthException('depth parameter must be greater than 0');
        }

        try {
            return json_decode($json, $associative, $depth, JSON_THROW_ON_ERROR | $flags);
        } catch (\Exception $e) {
            throw new MalformedDocumentException('Error while decoding JSON: ' . $e->getMessage(), null, $json, $e);
        }
    }

    /**
     * Converts a decoded document back to its json string
     *
     * @param mixed $document
     * @param int $depth
     * @param int $flags
     * @return string
     */
    private static function documentToString(mixed $document, int $flags = 0, int $depth = 512): string
    {
        if ($depth < 1) {
            throw new InvalidJsonDepthException('depth parameter must be greater than 0');
        }

        try {
            return json_encode($document, JSON_THROW_ON_ERROR | $flags, $depth);
        } catch (\Exception $e) {
            throw new MalformedDocumentException('Error while encoding JSON: ' . $e->getMessage(), null, null, $e);
        }
    }

    /**
     * Ensures that $pointer is a valid JSON Pointer
     *
     * @param string $pointer
     * @return void
     */
    private static function assertValidJsonPointer(string $pointer): void
    {
        if ($pointer !== '' && !str_starts_with($pointer, '/')) {
            throw new MalformedPathException(sprintf('path "%s" does not start with a slash', $pointer), $pointer);
        }
    }

    /**
     * Ensures that the given $node property/index exists in the $document
     *
     * @param array<int|string, mixed>|\stdClass $document
     * @param string $node
     * @param string[] $originalpath
     * @return void
     * @throws UnknownPathException
     */
    private static function assertPropertyExists(array|\stdClass $document, string $node, array $originalpath): void
    {
        $isObject = is_object($document);

        if ((($isObject && !property_exists($document, $node)) || (!$isObject && !array_key_exists($node, $document)))) {
            throw new UnknownPathException(sprintf('Unknown document path "/%s"', self::pathToString($originalpath)));
        }
    }

    /**
     * Tells if $a and $b are of the same JSON type
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.6
     * @param mixed $a
     * @param mixed $b
     * @return bool true if $a and $b are JSON equal, false otherwise
     */
    private static function isJsonEquals(mixed $a, mixed $b): bool
    {
        if (is_array($a) || is_object($a)) {
            $a = (array) $a;
            self::recursiveKeySort($a);
        }
        if (is_array($b) || is_object($b)) {
            $b = (array) $b;
            self::recursiveKeySort($b);
        }

        return self::documentToString($a) === self::documentToString($b);
    }

    /**
     * Applies ksort to each array element recursively
     *
     * @param array $a
     * @return void
     */
    private static function recursiveKeySort(array &$a): void
    {
        foreach ($a as &$item) {
            if (is_array($item) || is_object($item)) {
                $item = (array) $item;
                self::recursiveKeySort($item);
            }
        }

        ksort($a, SORT_STRING);
    }
}
