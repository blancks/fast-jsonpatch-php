<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\json\handlers;

use blancks\JsonPatch\exceptions\MalformedDocumentException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(BasicJsonHandler::class)]
#[UsesClass(MalformedDocumentException::class)]
#[UsesClass(JsonPointer6901::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ObjectAccessor::class)]
class BasicJsonHandlerTest extends TestCase
{
    private BasicJsonHandler $BasicJsonHandler;

    protected function setUp(): void
    {
        $this->BasicJsonHandler = new BasicJsonHandler();
        parent::setUp();
    }

    /**
     * Test method for the encode method of the BasicJsonHandler class.
     *
     * @return void
     */
    public function testEncode(): void
    {
        $document = ['key' => 'value'];
        $encodedDocument = $this->BasicJsonHandler->encode($document);
        $this->assertSame(json_encode($document), $encodedDocument);
    }

    /**
     * Test method for the scenario where the encode method of the BasicJsonHandler
     * class throws an exception.
     *
     * @return void
     */
    public function testEncodeThrowsException(): void
    {
        $document = "\xb11";
        $this->expectException(MalformedDocumentException::class);
        $this->BasicJsonHandler->encode($document);
    }

    /**
     * Test method for the decode method of the BasicJsonHandler class.
     *
     * @return void
     */
    public function testDecode(): void
    {
        $json = '{"key":"value"}';
        $decodedJson = $this->BasicJsonHandler->decode($json);
        $this->assertEquals(json_decode($json), $decodedJson);
    }

    /**
     * Test method for the scenario where the decode method of the BasicJsonHandler
     * class throws an exception.
     *
     * @return void
     */
    public function testDecodeThrowsException(): void
    {
        $json = "\xb11";
        $this->expectException(MalformedDocumentException::class);
        $this->BasicJsonHandler->decode($json);
    }
}
