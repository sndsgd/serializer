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
                "invalid options; expecting a string no longer than ".
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
     * Split a serialized payload into the identifier, options, and payload
     *
     * @param string &$payload The serialized payload
     * @return array<string>
     */
    public static function split(string &$payload): array
    {
        self::verifyPayloadLength($payload);
        return [
            substr($payload, 0, self::IDENTIFIER_LENGTH),
            substr($payload, self::OPTIONS_START_INDEX, self::OPTIONS_LENGTH),
            substr($payload, self::PAYLOAD_START_INDEX),
        ];
    }

    /**
     * Retrieve the serializer identifier from a serialized payload
     *
     * @param string &$payload The serialized payload
     * @return string
     */
    public static function getIdentifier(string &$payload): string
    {
        self::verifyPayloadLength($payload);
        return substr($payload, 0, self::IDENTIFIER_LENGTH);
    }

    /**
     * Verify a serialized payload meets the minimum length requirement
     *
     * @param string &$payload The serialized payload to verify
     * @return void
     * @throws \UnexpectedValueException If the minimum length is not met
     */
    private static function verifyPayloadLength(string &$payload)
    {
        if (strlen($payload) < self::MIN_LENGTH) {
            throw new \UnexpectedValueException(
                "provided payload did not meet minimum length requirement"
            );
        }
    }
}
