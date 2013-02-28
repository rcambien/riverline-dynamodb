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
    protected $nextContext;

    /**
     * The previous request count
     * @var int|null
     */
    protected $requestCount = 0;

    /**
     * @param Context\Collection|null $nextContext
     * @param int $requestCount The previous request count
     */
    function __construct(Context\Collection $nextContext = null, $requestCount = 0)
    {
        $this->nextContext  = $nextContext;
        $this->requestCount = $requestCount;
    }

    /**
     * Return the previous request last key
     * @return null|\Riverline\DynamoDB\Context\Query|\Riverline\DynamoDB\Context\Scan
     */
    public function getNextContext()
    {
        return $this->nextContext;
    }

    /**
     * Return true if the previous request has more items to retreive
     * @return bool
     */
    public function more()
    {
        return !empty($this->nextContext);
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
     * Merge a collection with the current collection
     * @param Collection $collection The collection to merge
     */
    public function merge(Collection $collection)
    {
        $this->requestCount += count($collection);
        foreach($collection as $item) {
            $this->add($item);
        }
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
        if (empty($this->items)) {
            // Collection from a count request
            return $this->requestCount;
        } else {
            // Real items count
            return count($this->items);
        }
    }
}