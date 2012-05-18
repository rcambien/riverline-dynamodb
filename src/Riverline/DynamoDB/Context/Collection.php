<?php

namespace Riverline\DynamoDB\Context;

/**
 * @class
 */
abstract class Collection extends Get
{
    /**
     * @var boolean
     */
    protected $count;

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var string
     */
    protected $lastKey;

    /**
     * @param boolean $count
     * @return \Riverline\DynamoDB\Context\Collection
     */
    public function setCount($count)
    {
        $this->count = (bool)$count;

        return $this;
    }

    /**
     * @param integer $limit
     * @return \Riverline\DynamoDB\Context\Collection
     */
    public function setLimit($limit)
    {
        $this->limit = intval($limit);

        return $this;
    }

    /**
     * @param string $lastKey
     * @return \Riverline\DynamoDB\Context\Collection
     */
    public function setLastKey($lastKey)
    {
        $this->lastKey = $lastKey;

        return $this;
    }

    /**
     * Return the context formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = parent::getForDynamoDB();

        $count = $this->count;
        if (null !== $count) {
            $parameters['Count'] = $this->count;
        }

        $limit = $this->limit;
        if (null !== $limit) {
            $parameters['Limit'] = $limit;
        }

        $lastKey = $this->lastKey;
        if (null !== $lastKey) {
            $parameters['ExclusiveStartKey'] = $lastKey;
        }

        return $parameters;
    }
}