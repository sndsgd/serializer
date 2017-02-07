<?php

namespace sndsgd\serializer;

class JsonSerializerTest extends \PHPUnit_Framework_TestCase
{
    use \phpmock\phpunit\PHPMock;

    protected $serializer;
    protected $largeObject;

    public function setup()
    {
        $this->serializer = new JsonSerializer();
        $this->largeObject = new \StdClass();
        $largeValue = \sndsgd\Str::random(JsonSerializer::MIN_COMPRESS_LENGTH);
        $this->largeObject->longProperty = $largeValue;
    }

    public function testIdentifierLength()
    {
        $length = strlen($this->serializer->getIdentifier());
        $this->assertSame(Payload::IDENTIFIER_LENGTH, $length);
    }

    /**
     * @dataProvider provideSerializeAndDeserialize
     */
    public function testSerializeAndDeserialize($object)
    {
        $serialized = $this->serializer->serialize($object);
        $deserialized = $this->serializer->deserialize($serialized);
        $this->assertEquals($object, $deserialized);
    }

    public function provideSerializeAndDeserialize(): array
    {
        $one = new \StdClass();

        $two = new \StdClass();
        $two->one = 1;
        $two->two = "two";
        $two->three = [1, 2, 3];

        $sub = new \SerializeMe();
        $three = new \SerializeMe($sub);

        return [
            [$one],
            [$two],
            [$this->largeObject],
            [$three],
        ];
    }

    /**
     * @expectedException RuntimeException
     */
    public function testDeflateException()
    {
        $functionMock = $this->getFunctionMock(__NAMESPACE__, "gzdeflate");
        $functionMock->expects($this->any())->willReturn(false);
        $this->serializer->serialize($this->largeObject);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInflateException()
    {
        $serialized = $this->serializer->serialize($this->largeObject);
        $functionMock = $this->getFunctionMock(__NAMESPACE__, "gzinflate");
        $functionMock->expects($this->any())->willReturn(false);
        $this->serializer->deserialize($serialized);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testJsonEncodeException()
    {
        $encodeMock = $this->getFunctionMock(__NAMESPACE__, "json_encode");
        $encodeMock->expects($this->any())->willReturn(null);
        $serialized = $this->serializer->serialize(new \StdClass());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testJsonDecodeException()
    {
        $serialized = $this->serializer->serialize(new \StdClass());
        $decodeMock = $this->getFunctionMock(__NAMESPACE__, "json_decode");
        $decodeMock->expects($this->any())->willReturn(null);
        $getErrorMock = $this->getFunctionMock(__NAMESPACE__, "json_last_error");
        $getErrorMock->expects($this->any())->willReturn(JSON_ERROR_RECURSION);
        $this->serializer->deserialize($serialized);
    }
}
