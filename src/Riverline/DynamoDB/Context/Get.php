<?php

namespace Riverline\DynamoDB\Context;

/**
 * @class
 */
class Get
{
    /**
     * List of attributes to get
     * @var array|null
     */
    protected $attributesToGet;

    /**
     * Use of the consistent read method
     * @var bool
     */
    protected $consistentRead;

    /**
     * @param array $attributesToGet The list of attributes to get
     * @return \Riverline\DynamoDB\Context\Get
     */
    public function setAttributesToGet($attributesToGet)
    {
        $this->attributesToGet = $attributesToGet;

        return $this;
    }

    /**
     * @param boolean $consistentRead Use the consistent read method
     * @return \Riverline\DynamoDB\Context\Get
     */
    public function setConsistentRead($consistentRead)
    {
        $this->consistentRead = $consistentRead;

        return $this;
    }

    /**
     * Return the context formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();

        $attributes = $this->attributesToGet;
        if (null !== $attributes) {
            $parameters['AttributesToGet'] = $attributes;
        }

        $consistentRead = $this->consistentRead;
        if (null !== $consistentRead) {
            $parameters['ConsistentRead'] = $consistentRead;
        }

        return $parameters;
    }
}