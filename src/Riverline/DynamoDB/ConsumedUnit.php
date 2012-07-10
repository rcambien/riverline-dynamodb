<?php

namespace Riverline\DynamoDB;

/**
 * @class
 */
class ConsumedUnit
{
    private $read = 0.0;

    private $write = 0.0;

    public function addRead($unit)
    {
        $this->read += floatval($unit);
    }

    public function addWrite($unit)
    {
        $this->write += floatval($unit);
    }

    public function getRead()
    {
        return $this->read;
    }

    public function getWrite()
    {
        return $this->write;
    }
}