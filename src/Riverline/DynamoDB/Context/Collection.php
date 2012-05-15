<?php

namespace Riverline\DynamoDB\Context;

/**
 * @class
 */
abstract class Collection extends Get
{
    protected $count;

    protected $limit;

    protected $lastKey;

    public function setCount($count)
    {
        $this->count = (bool)$count;

        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = intval($limit);

        return $this;
    }

    public function setLastKey($lastKey)
    {
        $this->lastKey = $lastKey;

        return $this;
    }

    public function setAttributesToGet($attributesToGet)
    {
        $this->attributesToGet = $attributesToGet;

        return $this;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getLastKey()
    {
        return $this->lastKey;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getAttributesToGet()
    {
        return $this->attributesToGet;
    }

    public function getForDynamoDB()
    {
        $parameters = parent::getForDynamoDB();

        if ($this->getCount()) {
            $parameters['Count'] = true;
        }

        $limit = $this->getLimit();
        if (null !== $limit) {
            $parameters['Limit'] = $limit;
        }

        $lastKey = $this->getLastKey();
        if (null !== $lastKey) {
            $parameters['ExclusiveStartKey'] = $lastKey;
        }

        return $parameters;
    }
}