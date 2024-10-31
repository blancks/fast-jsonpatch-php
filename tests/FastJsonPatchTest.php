<?php declare(strict_types=1);

namespace blancks\JsonPatchTest;

use blancks\JsonPatch\exceptions\FastJsonPatchException;
use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FastJsonPatch::class)]
#[UsesClass(FastJsonPatchException::class)]
#[UsesClass(UnknownPathException::class)]
final class FastJsonPatchTest extends JsonPatchCompliance
{
    public function testValidPatch(): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson('{"foo":"bar"}');
        $this->assertTrue($FastJsonPatch->isValidPatch('[{"op":"test","path":"/foo","value":"bar"}]'));
    }

    public function testInvalidPatch(): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson('{"foo":"bar"}');
        $this->assertFalse($FastJsonPatch->isValidPatch('{"op":"test","path":"/foo","value":"bar"}'));
        $this->assertFalse($FastJsonPatch->isValidPatch('[{"op":"add"}]'));
    }

    public function testUnknownPatchOperation(): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson('{"foo":"bar"}');
        $this->assertFalse($FastJsonPatch->isValidPatch('[{"op":"unknown","path":"/foo","value":"bar"}]'));
    }

    /**
     * @return void
     * @throws FastJsonPatchException
     */
    public function testAppyUnknownPatchOperationMustThrow(): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson('{"foo":"bar"}');
        $this->expectException(InvalidPatchException::class);
        $FastJsonPatch->apply('[{"op":"unknown","path":"/foo","value":"bar"}]');
    }

    #[DataProvider('jsonReadProvider')]
    public function testJsonPointerRead(string $document, string $pointer, null|string|bool $expected): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson($document);
        $this->assertSame($FastJsonPatch->read($pointer), $expected);
    }

    /**
     * @return array<string, array<int, bool|null|string>>
     */
    public static function jsonReadProvider(): array
    {
        return [
            'Read root document' => ['"foo"', '', 'foo'],
            'Read array leaf' => ['{"foo":["bar"]}', '/foo/0', 'bar'],
            'Read object leaf' => ['{"foo":"bar"}', '/foo', 'bar'],
            'Read boolean true' => ['{"foo":true}', '/foo', true],
            'Read boolean false' => ['{"foo":false}', '/foo', false],
            'Read null' => ['{"foo":null}', '/foo', null],
        ];
    }

    public function testInvalidJsonPointerRead(): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson('{"foo":"bar"}');
        $this->expectException(UnknownPathException::class);
        $FastJsonPatch->read('/bar');
    }

    /**
     * @return void
     * @throws FastJsonPatchException
     */
    public function testCustomOperationHandler(): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson('{}');
        $FastJsonPatch->registerOperation(new class() extends PatchOperation {
            public function getOperation(): string
            {
                return 'addexclamation';
            }

            public function validate(object $patch): void
            {
                $this->assertValidValue($patch);
            }

            /**
             * @param mixed $document
             * @param object{op:string,  path: string, value: mixed} $patch
             * @return void
             */
            public function apply(mixed &$document, object $patch): void
            {
                $this->JsonHandler->write($document, $patch->path, $patch->value . '!');
            }

            public function getRevertPatch(object $patch): ?array
            {
                return null;
            }
        });

        $FastJsonPatch->apply('[{"op": "addexclamation", "path": "/foo", "value": "Hello World"}]');
        $this->assertSame($FastJsonPatch->read('/foo'), 'Hello World!');
    }

    /**
     * @param string $json
     * @param string $patches
     * @param string $expected
     * @return void
     * @throws \JsonException
     * @throws FastJsonPatchException
     */
    #[DataProvider('validOperationsProvider')]
    public function testValidJsonPatches(string $json, string $patches, string $expected): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson($json);
        $FastJsonPatch->apply($patches);

        $this->assertSame(
            $this->normalizeJson($expected),
            $this->normalizeJson($this->jsonEncode($FastJsonPatch->getDocument()))
        );
    }

    /**
     * @param string $json
     * @param string $patches
     * @param string $expected
     * @return void
     * @throws \JsonException
     */
    #[DataProvider('atomicOperationsProvider')]
    public function testAtomicOperations(string $json, string $patches, string $expected): void
    {
        $FastJsonPatch = FastJsonPatch::fromJson($json);

        $this->expectException(FastJsonPatchException::class);
        $FastJsonPatch->apply($patches);

        $this->assertSame(
            $this->normalizeJson($expected),
            $this->normalizeJson($this->jsonEncode($FastJsonPatch->getDocument()))
        );
    }
}
