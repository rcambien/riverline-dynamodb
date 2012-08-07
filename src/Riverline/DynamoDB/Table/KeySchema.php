<?php

namespace Riverline\DynamoDB\Table;

/**
 * @class
 */
class KeySchema
{
    /**
     * HashKeyElement
     * @var \Riverline\DynamoDB\Table\KeySchemaElement
     */
    private $hash;

    /**
     * RangeKeyElement
     * @var \Riverline\DynamoDB\Table\KeySchemaElement
     */
    private $range;

    /**
     * @param \Riverline\DynamoDB\Table\KeySchemaElement $hash
     * @param \Riverline\DynamoDB\Table\KeySchemaElement $range
     */
    public function __construct(KeySchemaElement $hash, KeySchemaElement $range = null)
    {
        $this->hash  = $hash;
        $this->range = $range;
    }

    /**
     * Return HashKeyElement
     * @return \Riverline\DynamoDB\Table\KeySchemaElement
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Return RangeKeyElement
     * @return \Riverline\DynamoDB\Table\KeySchemaElement
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
        $hash  = $this->getHash();
        $range = $this->getRange();
        $parameters = array('HashKeyElement' => $hash->getForDynamoDB());
        if (null !== $range) {
            $parameters['RangeKeyElement'] = $range->getForDynamoDB();
        }
        return $parameters;
    }
}