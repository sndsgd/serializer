<?php

namespace sndsgd\serializer;

class PhpSerializerTest extends \PHPUnit_Framework_TestCase
{
    use \phpmock\phpunit\PHPMock;

    protected $serializer;
    protected $largeObject;

    public function setup()
    {
        $this->serializer = new PhpSerializer();
        $this->largeObject = new \StdClass();
        $largeValue = \sndsgd\Str::random(PhpSerializer::MIN_COMPRESS_LENGTH);
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

        return [
            [$one],
            [$two],
            [$this->largeObject],
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
    public function testUnserializeException()
    {
        $serialized = $this->serializer->serialize(new \StdClass());
        $functionMock = $this->getFunctionMock(__NAMESPACE__, "unserialize");
        $functionMock->expects($this->any())->willReturn(false);
        $this->serializer->deserialize($serialized);
    }
}
