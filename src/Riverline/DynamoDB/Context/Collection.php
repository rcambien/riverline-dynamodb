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
     * @var array
     */
    protected $exclusiveStartKey;

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
     * @param array $exclusiveStartKey
     * @return \Riverline\DynamoDB\Context\Collection
     */
    public function setExclusiveStartKey($exclusiveStartKey)
    {
        $this->exclusiveStartKey = $exclusiveStartKey;

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
            $parameters['Count'] = $count;
        }

        $limit = $this->limit;
        if (null !== $limit) {
            $parameters['Limit'] = $limit;
        }

        $exclusiveStartKey = $this->exclusiveStartKey;
        if (null !== $exclusiveStartKey) {
            $parameters['ExclusiveStartKey'] = $exclusiveStartKey;
        }

        return $parameters;
    }
}