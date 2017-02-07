<?php

namespace sndsgd\serializer;

class PayloadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideJoin
     */
    public function testJoin($identifier, $options, $data, $expect)
    {
        $result = Payload::join($identifier, $options, $data);
        $this->assertSame($expect, $result);
    }

    public function provideJoin(): array
    {
        return [
            ["aa", "b", "c", "aa   bc"],
        ];
    }

    /**
     * @dataProvider provideJoinException
     * @expectedException InvalidArgumentException
     */
    public function testJoinException($identifier, $options, $data)
    {
        Payload::join($identifier, $options, $data);
    }

    public function provideJoinException(): array
    {
        $identifier = str_repeat("a", Payload::IDENTIFIER_LENGTH);
        $shortIdentifier = str_repeat("b", Payload::IDENTIFIER_LENGTH - 1);
        $longIdentifier = str_repeat("c", Payload::IDENTIFIER_LENGTH + 1);

        $options = str_repeat("d", Payload::OPTIONS_LENGTH);
        $longOptions = str_repeat("e", Payload::OPTIONS_LENGTH + 1);

        return [
            [$shortIdentifier, "", ""],
            [$longIdentifier, "", ""],
            [$identifier, $longOptions, ""],
            [$identifier, $options, ""],
        ];
    }

    /**
     * @dataProvider provideSplit
     */
    public function testSplit($data, $expect)
    {
        $result = Payload::split($data);
        $this->assertSame($expect, $result);
    }

    public function provideSplit(): array
    {
        $identifier = str_repeat("a", Payload::IDENTIFIER_LENGTH);
        $options = str_repeat("b", Payload::OPTIONS_LENGTH);
        $data = str_repeat("c", 100);

        return [
            [$identifier.$options.$data, [$identifier, $options, $data]],
        ];
    }

    /**
     * @dataProvider provideSplitException
     * @expectedException InvalidArgumentException
     */
    public function testSplitException($test)
    {
        Payload::split($test);
    }

    public function provideSplitException(): array
    {
        return [
            [str_repeat("a", Payload::MIN_LENGTH - 1)],
        ];
    }
}
