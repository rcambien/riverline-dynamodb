<?php

namespace Riverline\DynamoDB\Context;

use \Riverline\DynamoDB\Attribute;

/**
 * @class
 */
class BatchWrite implements \Countable
{
    /**
     * @var array
     */
    protected $itemsToPut = array();

    /**
     * @var array
     */
    protected $keysToDelete = array();

    /**
     * @param \Riverline\DynamoDB\Item $item
     * @throws \Riverline\DynamoDB\Exception\AttributesException
     * @return BatchWrite
     */
    public function addItemToPut(\Riverline\DynamoDB\Item $item)
    {
        if ($this->count() >= 25) {
            throw new \Riverline\DynamoDB\Exception\AttributesException("Can't add more than 25 requests");
        }

        $this->itemsToPut[] = $item;
        return $this;
    }

    /**
     * @param $table
     * @param mixed $hash
     * @param mixed|null $range
     * @throws \Riverline\DynamoDB\Exception\AttributesException
     * @return BatchWrite
     */
    public function addKeyToDelete($table, $hash, $range = null)
    {
        if ($this->count() >= 25) {
            throw new \Riverline\DynamoDB\Exception\AttributesException("Can't add more than 25 requests");
        }

        $this->keysToDelete[] = array($table, $hash, $range);
        return $this;
    }

    /**
     * @see \Countable
     * @return int
     */
    public function count()
    {
        return count($this->itemsToPut) + count($this->keysToDelete);
    }

    /**
     * Return the context formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();

        // Items to put
        foreach ($this->itemsToPut as $item) {
            /** @var $item \Riverline\DynamoDB\Item */
            if (!isset($parameters[$item->getTable()])) {
                $parameters[$item->getTable()] = array();
            }

            $attributes = array();
            foreach ($item as $name => $attribute) {
                /** @var $attribute \Riverline\DynamoDB\Attribute */
                if ("" !== $attribute->getValue()) {
                    // Only not empty string
                    $attributes[$name] = $attribute->getForDynamoDB();
                }
            }

            $parameters[$item->getTable()][] = array(
                'PutRequest' => array('Item' => $attributes)
            );
        }

        // Keys to delete
        foreach ($this->keysToDelete as $key) {
            list ($table, $hash, $range) = $key;

            if (!isset($parameters[$table])) {
                $parameters[$table] = array();
            }

            $hash = new Attribute($hash);
            $key = array(
                'HashKeyElement' => $hash->getForDynamoDB()
            );

            if (!is_null($range)) {
                $range = new Attribute($range);
                $key['RangeKeyElement'] = $range->getForDynamoDB();
            }

            $parameters[$table][] = array(
                'DeleteRequest' => array('Key' => $key)
            );
        }

        return array('RequestItems' => $parameters);
    }
}