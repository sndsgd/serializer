<?php

require __DIR__."/../../vendor/autoload.php";
require __DIR__."/../resources/SerializeMe.php";

# create mocks for the following namspaced functions
# this way we don't have to worry about them being called first
# see https://github.com/php-mock/php-mock#requirements-and-restrictions
$mockFunctions = [
    ["sndsgd\\serializer", "gzdeflate"],
    ["sndsgd\\serializer", "gzinflate"],
    ["sndsgd\\serializer", "unserialize"],
    ["sndsgd\\serializer", "json_decode"],
    ["sndsgd\\serializer", "json_encode"],
    ["sndsgd\\serializer", "json_last_error"],
    ["sndsgd\\serializer", "extension_loaded"],
    ["sndsgd\\serializer", "igbinary_unserialize"],
];

foreach ($mockFunctions as list($namespace, $name)) {
    (new \phpmock\MockBuilder())
        ->setNamespace($namespace)
        ->setName($name)
        ->setFunction(function(){})
        ->build()
        ->define();
}
