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
    protected $ConsistentRead;

    /**
     * @param array $attributesToGet The list of attributes to get
     */
    public function setAttributesToGet($attributesToGet)
    {
        $this->attributesToGet = $attributesToGet;
    }

    /**
     * Return the list ofattributes to get
     * @return array|null
     */
    public function getAttributesToGet()
    {
        return $this->attributesToGet;
    }

    /**
     * @param bool $ConsistentRead Use the consistent read method
     */
    public function setConsistentRead($ConsistentRead)
    {
        $this->ConsistentRead = $ConsistentRead;
    }

    /**
     * Return true to use the consistent read method
     * @return bool
     */
    public function getConsistentRead()
    {
        return $this->ConsistentRead;
    }

    /**
     * Return the context formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();

        $attributes = $this->getAttributesToGet();
        if (null !== $attributes) {
            $parameters['AttributesToGet'] = $attributes;
        }

        $ConsistentRead = $this->getConsistentRead();
        if (null !== $ConsistentRead) {
            $parameters['ConsistentRead'] = $ConsistentRead;
        }

        return $parameters;
    }
}