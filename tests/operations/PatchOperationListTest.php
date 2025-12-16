<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\operations;

use blancks\JsonPatch\exceptions\FastJsonPatchExceptionTrait;
use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\handlers\JsonHandlerInterface;
use blancks\JsonPatch\operations\Add;
use blancks\JsonPatch\operations\Copy;
use blancks\JsonPatch\operations\Move;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\PatchOperationList;
use blancks\JsonPatch\operations\Remove;
use blancks\JsonPatch\operations\Replace;
use blancks\JsonPatch\operations\Test;
use blancks\JsonPatchTest\JsonPatchCompliance;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Assert;
use stdClass;
use Throwable;

#[CoversClass(PatchOperationList::class)]
#[CoversClass(Add::class)]
#[CoversClass(Copy::class)]
#[CoversClass(Move::class)]
#[CoversClass(Remove::class)]
#[CoversClass(Replace::class)]
#[CoversClass(Test::class)]
#[CoversClass(PatchOperation::class)]
#[UsesClass(FastJsonPatchExceptionTrait::class)]
#[UsesClass(InvalidPatchException::class)]
#[UsesClass(InvalidPatchOperationException::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
class PatchOperationListTest extends JsonPatchCompliance
{
    /**
     * @return array<string, array{list<PatchOperation>, string}>
     */
    public static function validEncodeDecodeProvider(): array
    {
        $objValue = new stdClass();
        $objValue->type = 'foo';
        $objValue->list = ['bar', 'baz'];

        return [
            'empty list' => [
                [],
                '[]',
            ],
            'single operation' => [
                [
                    new Add(path: '/foo', value: 'World'),
                ],
                '[{"op": "add", "path": "/foo", "value": "World"}]',
            ],
            'all supported operations' => [
                [
                    new Add(path: '/bar', value: 'Worldy'),
                    new Copy(path: '/foo', from: '/bar'),
                    new Move(path: '/food', from: '/foo'),
                    new Remove(path: '/food'),
                    new Replace(path: '/bar', value: 'World'),
                    new Test(path: '/bar', value: 'World'),
                ],
                <<<'JSON'
                [
                  {"op":"add","path":"/bar","value":"Worldy"},
                  {"op":"copy","path":"/foo","from":"/bar"},
                  {"op":"move","path":"/food","from":"/foo"},
                  {"op":"remove","path":"/food"},
                  {"op":"replace","path":"/bar","value":"World"},
                  {"op":"test","path":"/bar","value":"World"}
                ]
                JSON,
            ],
            'operations with object values' => [
                [
                    new Add(path: '/bar', value: $objValue),
                    new Replace(path: '/bar', value: $objValue),
                    new Test(path: '/bar', value: $objValue),
                ],
                <<<'JSON'
                [
                  {"op":"add","path":"/bar","value":{"type":"foo","list":["bar","baz"]}},
                  {"op":"replace","path":"/bar","value":{"type":"foo","list":["bar","baz"]}},
                  {"op":"test","path":"/bar","value":{"type":"foo","list":["bar","baz"]}}
                ]
                JSON,
            ]
        ];
    }

    /**
     * @param list<PatchOperation> $operationDTOs
     * @param string $jsonOperations
     * @return void
     * @throws \JsonException
     */
    #[DataProvider('validEncodeDecodeProvider')]
    public function testItCanBeEncodedToJsonPatch(array $operationDTOs, string $jsonOperations): void
    {
        $this->assertSame(
            $this->normalizeJson($jsonOperations),
            $this->normalizeJson($this->jsonEncode(new PatchOperationList(...$operationDTOs))),
        );
    }

    /**
     * @param list<PatchOperation> $operationDTOs
     * @param string $jsonOperations
     * @return void
     * @throws \JsonException
     */
    #[DataProvider('validEncodeDecodeProvider')]
    public function testItCanBeBuiltFromEncodedJson(array $operationDTOs, string $jsonOperations): void
    {
        $this->assertEquals(
            new PatchOperationList(...$operationDTOs),
            PatchOperationList::fromJson($jsonOperations),
        );
    }

    public function testItCanUseCustomHandlerWhenDecodingFromJson(): void
    {
        $result = PatchOperationList::fromJson(
            '[fake]',
            jsonHandler: new class implements JsonHandlerInterface {
                public function write(mixed &$document, string $path, mixed $value): mixed
                {
                    throw new \BadMethodCallException(__METHOD__);
                }

                public function &read(mixed &$document, string $path): mixed
                {
                    throw new \BadMethodCallException(__METHOD__);
                }

                public function update(mixed &$document, string $path, mixed $value): mixed
                {
                    throw new \BadMethodCallException(__METHOD__);
                }

                public function delete(mixed &$document, string $path): mixed
                {
                    throw new \BadMethodCallException(__METHOD__);
                }

                public function encode(mixed $document, array $options = []): string
                {
                    throw new \BadMethodCallException(__METHOD__);
                }

                public function decode(string $json, array $options = []): mixed
                {
                    Assert::assertSame(
                        [
                            'json' => '[fake]',
                            'options' => [],
                        ],
                        get_defined_vars(),
                        'JSONHandler should have been called with expected args',
                    );
                    return [
                        (object) ['op' => 'remove', 'path' => '/some/path'],
                    ];
                }
            },
        );

        $this->assertEquals(
            new PatchOperationList(new Remove('/some/path')),
            $result,
        );
    }

    public function testItCanDecodeWithCustomOperationClasses(): void
    {
        $result = PatchOperationList::fromJson(
            <<<'JSON'
            [
              {"op":  "add", "path": "/greeting", "value": "Hello", "custom": "my own var" },
              {"op":  "append", "path": "/greeting", "suffix": " World" },
              {"op":  "copy", "path": "/whatever", "from": "/greeting"}
            ]
            JSON,
            customClasses: [
                'add' => CustomAdd::class,
                'append' => Append::class,
            ],
        );

        $this->assertEquals(
            new PatchOperationList(
                new CustomAdd('/greeting', 'Hello', 'my own var'),
                new Append('/greeting', ' World'),
                new Copy(path: '/whatever', from: '/greeting'),
            ),
            $result,
        );
    }

    /**
     * @return array<string, array{string, class-string<Throwable>, string}>
     */
    public static function invalidJsonProvider(): array
    {
        return [
            'json is not a list (example 1)' => [
                '{"some": "field"}',
                InvalidPatchException::class,
                'Invalid patch structure (expected list, got stdClass)',
            ],
            'json is not a list (example 2)' => [
                'true',
                InvalidPatchException::class,
                'Invalid patch structure (expected list, got bool)',
            ],
            'json is not a list (example 3)' => [
                '{"2": {"op": "add", "path": "/some/path", "value": "World"}}',
                InvalidPatchException::class,
                'Invalid patch structure (expected list, got stdClass)',
            ],
            'patch item is not an object (example 1)' => [
                '[["foo"]]',
                InvalidPatchOperationException::class,
                'Each patch item must be an object, got array',
            ],
            'patch item is not an object (example 2)' => [
                '[{"op": "remove", "path": "/some/path"}, true]',
                InvalidPatchOperationException::class,
                'Each patch item must be an object, got bool',
            ],
            'patch without op property' => [
                '[{"path": "/some/path", "value": "anything"}]',
                InvalidPatchOperationException::class,
                'Each patch item must specify "op"',
            ],
            'op is invalid type' => [
                '[{"op": true}]',
                InvalidPatchOperationException::class,
                'Patch "op" must be a string, got bool',
            ],
            'unknown operation' => [
                '[{"op": "scramble", "path": "/anywhere"}]',
                InvalidPatchOperationException::class,
                'Unknown operation "scramble"',
            ],
            'unexpected extra operation property' => [
                '[{"op":"add", "path": "/foo", "value": "bar", "custom": "things"}]',
                InvalidPatchOperationException::class,
                // The message also contains the original PHP message but this varies between versions so we do not
                // include it in the assertion.
                sprintf(
                    'Unexpected param(s) for add operation as %s',
                    Add::class,
                ),
            ],
            'missing operation property' => [
                '[{"op":"add", "path": "/foo"}]',
                InvalidPatchOperationException::class,
                // The message also contains the original PHP message but this varies between versions so we do not
                // include it in the assertion.
                sprintf(
                    'Missing required param(s) for add operation as %s',
                    Add::class,
                ),
            ],
            'operation property has incorrect type' => [
                '[{"op":"add", "path": true, "value": "bar"}]',
                InvalidPatchOperationException::class,
                // The message also contains the original PHP message but this varies between versions so we do not
                // include it in the assertion.
                sprintf(
                    'Invalid param(s) for add operation as %s',
                    Add::class,
                ),
            ],
            'operation with mixed string and int keys (example 1)' => [
                // PHP would internally convert this into positional args and accept it even though there's no guarantee
                // we have the right keys in the right sequence / have no extra properties.
                '[{"op":"add", "0": "bar", "1": "/from"}]',
                InvalidPatchOperationException::class,
                'All patch operation properties must have string names'
            ],
            'operation with mixed string and int keys (example 2)' => [
                // PHP would internally convert this into positional args and accept it even though there's no guarantee
                // we have the right keys in the right sequence / have no extra properties.
                '[{"op":"add", "1": "bar", "2": "/from"}]',
                InvalidPatchOperationException::class,
                'All patch operation properties must have string names'
            ]
        ];
    }

    /**
     * @param string $json
     * @param class-string<Throwable> $expect_exception
     * @param string $expect_msg
     * @return void
     */
    #[DataProvider('invalidJsonProvider')]
    public function testItThrowsOnAttemptToCreateFromInvalidJson(string $json, string $expect_exception, string $expect_msg): void
    {
        $this->expectException($expect_exception);
        $this->expectExceptionMessage($expect_msg);
        PatchOperationList::fromJson($json);
    }
}

readonly class Append extends PatchOperation
{
    public function __construct(
        public string $path,
        public string $suffix,
    ) {
        parent::__construct('append');
    }
}

readonly class CustomAdd extends PatchOperation
{
    public function __construct(
        public string $path,
        public mixed $value,
        public mixed $custom,
    ) {
        parent::__construct('add');
    }
}
