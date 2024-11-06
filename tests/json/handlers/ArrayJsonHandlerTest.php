<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\json\handlers;

use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\handlers\ArrayJsonHandler;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayJsonHandler::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
class ArrayJsonHandlerTest extends TestCase
{
    /**
     * @var ArrayJsonHandler
     */
    private ArrayJsonHandler $ArrayJsonHandler;

    protected function setUp(): void
    {
        $this->ArrayJsonHandler = new ArrayJsonHandler();
        parent::setUp();
    }

    public function testDecode(): void
    {
        $json = '{"name":"John","age":30,"city":"New York"}';
        $result = $this->ArrayJsonHandler->decode($json);
        $this->assertIsArray($result);
        $this->assertSame(['name' => 'John', 'age' => 30, 'city' => 'New York'], $result);
    }
}
