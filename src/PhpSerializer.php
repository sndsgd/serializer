<?php

namespace sndsgd\serializer;

/**
 * A serializer that uses PHP's `serialize()` and `unserialize()`
 */
class PhpSerializer implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return "00";
    }

    /**
     * @inheritDoc
     */
    public function serialize($object): string
    {
        $payload = serialize($object);
        $options = 0;

        # compress the payload if its long enough
        if (strlen($payload) >= self::MIN_COMPRESS_LENGTH) {
            $options |= self::OPTION_IS_COMPRESSED;
            $payload = gzdeflate($payload);
            if ($payload === false) {
                throw new \RuntimeException("failed to compress payload");
            }
        }

        return Payload::join($this->getIdentifier(), $options, $payload);
    }

    /**
     * @inheritDoc
     */
    public function deserialize(string $data)
    {
        list($identifier, $options, $payload) = Payload::split($data);

        $options = (int) $options;

        # if the jobData is compressed, decompress it
        if ($options & self::OPTION_IS_COMPRESSED) {
            $payload = gzinflate($payload);
            if ($payload === false) {
                throw new \RuntimeException("failed to decompress payload");
            }
        }

        $result = unserialize($payload);
        if ($result === false) {
            throw new \RuntimeException("failed to deserialize payload");
        }

        return $result;
    }
}
