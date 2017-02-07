<?php

namespace sndsgd\serializer;

/**
 * Serializers are used to convert jobs to strings and back to job instances
 */
interface SerializerInterface
{
    /**
     * The minimum length a stringified workload must be to be compressed
     *
     * @var int
     */
    const MIN_COMPRESS_LENGTH = 4096;

    /**
     * Bitmask value that indicates the workload is compressed
     *
     * @var int
     */
    const OPTION_IS_COMPRESSED = 1;

    /**
     * Retrieve the identifier for this serializer
     *
     * Identifiers are prepended to the serialized data as an indicator of how
     * the workload should be deserialized
     *
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * Serialize an object into a string
     *
     * @param object $object The object to serialize
     * @return string
     */
    public function serialize($object): string;

    /**
     * Deserialize an object encoded as a string back into an object
     *
     * @param string $data The data to deserialize
     * @return mixed
     */
    public function deserialize(string $data);
}
