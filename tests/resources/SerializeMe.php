<?php

class SerializeMe
{
    private $private = "private";
    protected $protected = "protected";
    public $public = "public";
    protected $array = [1, 2, 3, 4];
    protected $object;

    public function __construct(SerializeMe $object = null)
    {
        $this->object = $object;
    }
}
