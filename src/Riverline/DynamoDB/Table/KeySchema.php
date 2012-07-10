<?php

namespace Riverline\DynamoDB\Table;

/**
 * @class
 */
class KeySchema
{
    /**
     * HashKeyElement
     * @var KeySchemaElement
     */
    private $hash;

    /**
     * RangeKeyElement
     * @var KeySchemaElement
     */
    private $range;

    /**
     * @param KeySchemaElement $hashKeyElement HashKeyElement
     * @param KeySchemaElement $rangeKeyElement RangeKeyElement
     */
    public function __construct($hash, $range = null)
    {
        $this->hash = $hash;
        $this->range = $range;
    }

    /**
     * Return HashKeyElement
     * @return KeySchemaElement
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Return RangeKeyElement
     * @return KeySchemaElement
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Return schema formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $hash = $this->getHash();
        $range = $this->getRange();
        $parameters = array('HashKeyElement' => $hash->getForDynamoDB());
        if (null !== $range) {
            $parameters['RangeKeyElement'] = $range->getForDynamoDB();
        }
        return $parameters;
    }

    /**
     * Populate key schema from the raw DynamoDB response
     * @param \stdClass $data
     */
    public function populateFromDynamoDB(\stdClass $data)
    {
        $hash = new KeySchemaElement(null, null);
        $hash->populateFromDynamoDB($data->HashKeyElement);
        $this->hash = $hash;
        if (isset($data->RangeKeyElement)) {
            $range = new KeySchemaElement(null, null);
            $range->populateFromDynamoDB($data->RangeKeyElement);
            $this->range = $range;
        }
    }
}