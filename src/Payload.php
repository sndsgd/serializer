<?php

namespace sndsgd\serializer;

class Payload
{
    /**
     * The length of the serializer identifier
     *
     * @var int
     */
    const IDENTIFIER_LENGTH = 2;

    /**
     * The length of the serializer options
     *
     * @var int
     */
    const OPTIONS_LENGTH = 4;

    /**
     * The character position of the first options character
     *
     * @var int
     */
    const OPTIONS_START_INDEX = self::IDENTIFIER_LENGTH;

    /**
     * The character position of the first payload character
     *
     * @var int
     */
    const PAYLOAD_START_INDEX = self::OPTIONS_START_INDEX + self::OPTIONS_LENGTH;

    /**
     * The minimum length for any payload
     *
     * @var int
     */
    const MIN_LENGTH = self::IDENTIFIER_LENGTH + self::OPTIONS_LENGTH + 1;

    /**
     * Join serialized data into a single string
     *
     * @param string $identifier The serializer identifier
     * @param string $options The serializer options
     * @param string $payload The serialized payload
     * @return string
     */
    public static function join(
        string $identifier,
        string $options,
        string $payload
    ): string
    {
        if (strlen($identifier) !== self::IDENTIFIER_LENGTH) {
            throw new \InvalidArgumentException(
                "invalid identifier; expecting a string exactly ".
                self::IDENTIFIER_LENGTH." characters long"
            );
        }

        $optionsLength = strlen($options);
        if ($optionsLength > self::OPTIONS_LENGTH) {
            throw new \InvalidArgumentException(
                "invalid options; expecting a string exactly ".
                self::OPTIONS_LENGTH." characters long"
            );
        }

        if (empty($payload)) {
            throw new \InvalidArgumentException(
                "invalid payload; expecting a string at least one character long"
            );
        }

        # pad the options so they take up the full space
        if ($optionsLength < self::OPTIONS_LENGTH) {
            $options = str_pad($options, self::OPTIONS_LENGTH, " ", STR_PAD_LEFT);
        }

        return $identifier.$options.$payload;
    }

    /**
     * Split serialized data into the identifier, options, and payload
     *
     * @param string $data The serialized data
     * @return array
     */
    public static function split(string $data): array
    {
        $length = strlen($data);
        if ($length < self::MIN_LENGTH) {
            throw new \InvalidArgumentException(
                "provided payload did not meet minimum length requirement"
            );
        }

        return [
            substr($data, 0, self::IDENTIFIER_LENGTH),
            substr($data, self::OPTIONS_START_INDEX, self::OPTIONS_LENGTH),
            substr($data, self::PAYLOAD_START_INDEX),
        ];
    }
}
