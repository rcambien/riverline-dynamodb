<?php

namespace Riverline\DynamoDB\Context;

use \Riverline\DynamoDB\Attribute;

/**
 * @class
 */
class BatchGet
{
    /**
     * List of keys to get grouped by table
     * @var array|null
     */
    protected $keysByTable;

    /**
     * List of attributes to get grouped by table
     * @var array|null
     */
    protected $attributesToGetByTable;

    /**
     * Add an item key
     * @param string $table
     * @param string|integer $hash
     * @param string|integer|null $range
     * @throws \Riverline\DynamoDB\Exception\AttributesException
     * @return \Riverline\DynamoDB\Context\BatchGet
     */
    public function addKey($table, $hash, $range = null)
    {
        if (count($this->keysByTable, COUNT_RECURSIVE) >= 100) {
            throw new \Riverline\DynamoDB\Exception\AttributesException("Can't request more than 100 items");
        }

        if (!isset($this->keysByTable[$table])) {
            $this->keysByTable[$table] = array();
        }

        $this->keysByTable[$table][] = array($hash, $range);

        return $this;
    }

    /**
     * Set the attributes to get for a table's items
     * @param $table
     * @param array $attributesToGet The list of attributes to get
     * @return \Riverline\DynamoDB\Context\Get
     */
    public function setAttributesToGet($table, $attributesToGet)
    {
        $this->attributesToGetByTable[$table] = $attributesToGet;

        return $this;
    }

    /**
     * Return the context formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();

        foreach ($this->keysByTable as $table => $keys) {
            $parameters[$table] = array(
                'Keys' => array()
            );

            foreach ($keys as $key) {
                list($hash, $range) = $key;

                $formatedKey = array();

                // Convert to attribute
                $hash = new Attribute($hash);
                $formatedKey['HashKeyElement'] = $hash->getForDynamoDB();

                // Range key
                if (null !== $range) {
                    $range = new Attribute($range);
                    $formatedKey['RangeKeyElement'] = $range->getForDynamoDB();
                }

                $parameters[$table]['Keys'][] = $formatedKey;
            }

            if (isset($this->attributesToGetByTable[$table])) {
                $parameters[$table]['AttributesToGet'] = $this->attributesToGetByTable[$table];
            }

        }

        return array('RequestItems' => $parameters);
    }
}