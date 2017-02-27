<?php

namespace sndsgd\serializer;

/**
 * A serializer that uses PHP's igbinary extension
 */
class IgbinarySerializer implements SerializerInterface
{
    public function __construct()
    {
        if (!extension_loaded("igbinary")) {
            throw new \RuntimeException("missing required extension 'igbinary'");
        }
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return "02";
    }

    /**
     * @inheritDoc
     */
    public function serialize($object): string
    {
        $payload = igbinary_serialize($object);
        $options = 0;
        return Payload::join($this->getIdentifier(), $options, $payload);
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $data)
    {
        list($identifier, $options, $payload) = Payload::split($data);

        $result = igbinary_unserialize($payload);
        if ($result === false) {
            throw new \RuntimeException("failed to deserialize payload");
        }

        return $result;
    }
}
