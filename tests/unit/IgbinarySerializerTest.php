<?php

namespace sndsgd\serializer;

class IgbinarySerializerTest extends \PHPUnit_Framework_TestCase
{
    use \phpmock\phpunit\PHPMock;

    protected $serializer;
    protected $largeObject;

    public function setup()
    {
        if (!extension_loaded("igbinary")) {
            $this->markTestSkipped("the igbinary extension is not available");
        }

        $this->serializer = new IgbinarySerializer();
        $this->largeObject = new \StdClass();
        $largeValue = \sndsgd\Str::random(PhpSerializer::MIN_COMPRESS_LENGTH);
        $this->largeObject->longProperty = $largeValue;
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testConstructorException()
    {
        $functionMock = $this->getFunctionMock(__NAMESPACE__, "extension_loaded");
        $functionMock->expects($this->any())->willReturn(false);

        new IgbinarySerializer();
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
    public function testUnserializeException()
    {
        $serialized = $this->serializer->serialize(new \StdClass());
        $functionMock = $this->getFunctionMock(__NAMESPACE__, "igbinary_unserialize");
        $functionMock->expects($this->any())->willReturn(false);
        $this->serializer->deserialize($serialized);
    }
}
