<?php

namespace Riverline\DynamoDB;

/**
 * @class
 */
class Collection implements \IteratorAggregate, \Countable
{
    /**
     * The items collection
     * @var array
     */
    protected $items = array();

    /**
     * The previous request last key
     * @var string|null
     */
    protected $lastKey;

    /**
     * @param string|null $lastKey The previous request last key
     */
    function __construct($lastKey = null)
    {
        $this->lastKey = $lastKey;
    }

    /**
     * Return the previous request last key
     * @return null|string
     */
    public function getLastKey()
    {
        return $this->lastKey;
    }

    /**
     * Return true if the previous request has more items to retreive
     * @return bool
     */
    public function more()
    {
        return !empty($this->lastKey);
    }

    /**
     * Add an item to the collection
     * @param Item $item
     */
    public function add(Item $item)
    {
        $this->items[] = $item;
    }

    /**
     * Remove an item off the beginning of the collection
     * @return Item
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * @see \IteratorAggregate
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @see \Countable
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
}