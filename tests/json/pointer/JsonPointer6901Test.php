<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\json\pointer;

use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonPointer6901::class)]
class JsonPointer6901Test extends TestCase
{
    private JsonPointerHandlerInterface $JsonPointerHandler;

    public function setUp(): void
    {
        $this->JsonPointerHandler = new JsonPointer6901();
    }

    public function testIsValidPointerWithValidPointers(): void
    {
        $validPointers = [
            '',
            '/foo',
            '/foo/bar',
        ];

        foreach ($validPointers as $pointer) {
            $this->assertTrue(
                $this->JsonPointerHandler->isValidPointer($pointer),
                "Failed asserting that '$pointer' is a valid pointer."
            );
        }
    }

    public function testIsValidPointerWithInvalidPointers(): void
    {
        $invalidPointers = ['foo', 'foo/bar', ' ', 'foo/bar/'];

        foreach ($invalidPointers as $pointer) {
            $this->assertFalse(
                $this->JsonPointerHandler->isValidPointer($pointer),
                "Failed asserting that '$pointer' is an invalid pointer."
            );
        }
    }

    public function testGetTokensFromPointerWithEmptyPointer(): void
    {
        $this->assertSame([], $this->JsonPointerHandler->getTokensFromPointer(''));
    }

    public function testGetTokensFromPointerWithValidPointer(): void
    {
        $this->assertSame(['foo', 'bar'], $this->JsonPointerHandler->getTokensFromPointer('/foo/bar'));
    }

    public function testGetTokensFromPointerWithEscapingSequences(): void
    {
        $validPointers = [
            '/foo' => ['foo'],
            '/foo/0' => ['foo', '0'],
            '/' => [''],
            '/a~1b' => ['a/b'],
            '/c%d' => ['c%d'],
            '/e^f' => ['e^f'],
            '/g|h' => ['g|h'],
            '/i\\\\j' => ['i\\\\j'],
            '/k\"l' => ['k\"l'],
            '/ ' => [' '],
            '/m~0n' => ['m~n'],
        ];

        foreach ($validPointers as $pointer => $expected) {
            $this->assertSame(
                $expected,
                $this->JsonPointerHandler->getTokensFromPointer($pointer),
                "Failed asserting that '$pointer' equals the following tokens " . print_r($expected, true)
            );
        }
    }
}
