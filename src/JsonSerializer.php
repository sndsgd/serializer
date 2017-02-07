<?php

namespace sndsgd\serializer;

/**
 * Serializer that uses `json_encode()` and `json_decode()` to encode properties
 */
class JsonSerializer implements SerializerInterface
{
    /**
     * The property that is used to store the class name
     *
     * @var string
     */
    protected $classProperty;

    /**
     * Once reflection properties are retrieved, they are cached here
     *
     * @var array<string,array<\ReflectionProperty>>
     */
    protected $reflectionProperties = [];

    /**
     * @param string $classProperty The object class name property
     */
    public function __construct(string $classProperty = ":CLASS:")
    {
        $this->classProperty = $classProperty;
    }

    /**
     * @inheritDoc
     */
    public function getIdentifier(): string
    {
        return "01";
    }

    /**
     * @inheritDoc
     */
    public function serialize($object): string
    {
        # create a simple object that contains the class name as a property
        $data = $this->prepareObjectForJsonEncode($object);

        # json encode the
        $payload = json_encode($data, \sndsgd\Json::SIMPLE);
        if ($payload === null) {
            throw new \RuntimeException(
                "failed to serialize properites to JSON; ".
                json_last_error_msg()
            );
        }

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

        # if the data is compressed, decompress it
        if ($options & self::OPTION_IS_COMPRESSED) {
            $payload = gzinflate($payload);
            if ($payload === false) {
                throw new \RuntimeException("failed to decompress payload");
            }
        }

        $data = json_decode($payload);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                "failed to decode JSON; ".json_last_error_msg()
            );
        }

        return $this->createInstance($data);
    }

    /**
     * Retrieve reflection properties for a class from a cache
     *
     * This has a negative performance inpact for low volume,
     *
     * @param string $class The class to retrieve properties for
     * @return array<\ReflectionProperty>
     */
    protected function getPropertiesForClass(string $class)
    {
        if (!isset($this->reflectionProperties[$class])) {
            $reflection = new \ReflectionClass($class);
            $properties = $reflection->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
            }
            $this->reflectionProperties[$class] = $properties;
        }
        return $this->reflectionProperties[$class];
    }

    /**
     * Update an object to include the class name as a property
     *
     * @param object $object The object to prepare for json encode
     * @return \StdClass
     */
    protected function prepareObjectForJsonEncode($object)
    {
        if ($object instanceof \StdClass || $object === null) {
            return $object;
        }

        $class = get_class($object);

        # create a new generic object and add the class name as a property
        $ret = new \StdClass();
        $ret->{$this->classProperty} = $class;

        foreach ($this->getPropertiesForClass($class) as $property) {
            $name = $property->getName();
            $value = $property->getValue($object);
            if (is_object($value)) {
                $value = $this->prepareObjectForJsonEncode($value);
            }
            $ret->$name = $value;
        }

        return $ret;
    }

    /**
     * Create an instance of a class from an object that contains a class name
     * as a special property value
     *
     * @param object $data An object
     * @return object
     */
    protected function createInstance($data)
    {
        $class = $data->{$this->classProperty} ?? null;
        if ($class === null) {
            return $data;
        }

        # create a new instance of the object class using reflection
        $reflection = new \ReflectionClass($class);
        $ret = $reflection->newInstanceWithoutConstructor();

        foreach ($this->getPropertiesForClass($class) as $property) {
            $name = $property->getName();
            if (!isset($data->$name)) {
                continue;
            }

            $value = $data->$name;
            if (is_object($value)) {
                $property->setValue($ret, $this->createInstance($value));
            } else {
                $property->setValue($ret, $data->$name);
            }
        }

        return $ret;
    }
}
