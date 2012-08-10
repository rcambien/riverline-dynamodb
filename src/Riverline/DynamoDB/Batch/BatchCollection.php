<?php

namespace Riverline\DynamoDB\Batch;

use Riverline\DynamoDB\Collection;
use Riverline\DynamoDB\Exception\AttributesException;

/**
 * @class
 */
class BatchCollection implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * The items collection
     * @var array
     */
    protected $collections = array();

    /**
     * The previous request unprocessed keys
     * @var \Riverline\DynamoDB\Context\BatchGet|null
     */
    protected $unprocessedKeysContext = null;

    /**
     * @param \Riverline\DynamoDB\Context\BatchGet|null $unprocessedKeys
     */
    function __construct(\Riverline\DynamoDB\Context\BatchGet $unprocessedKeysContext = null)
    {
        $this->unprocessedKeysContext = $unprocessedKeysContext;
    }

    /**
     * Add an items collection
     * @param string $table
     * @param \Riverline\DynamoDB\Collection $collection
     */
    public function setItems($table, Collection $collection)
    {
        $this->collections[$table] = $collection;
    }

    /**
     * Return the previous request unprocessedKeys
     * @return mixed
     */
    public function getUnprocessedKeysContext()
    {
        return $this->unprocessedKeysContext;
    }

    /**
     * Return true if the previous request has more items to retreive
     * @return bool
     */
    public function more()
    {
        return !empty($this->unprocessedKeysContext);
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->collections[$offset]);
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return (isset($this->collections[$offset])?$this->collections[$offset]:null);
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     * @param $value
     * @throws \Riverline\DynamoDB\Exception\AttributesException
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new AttributesException('Square bracket syntax isn\'t allowed');
        }

        $this->setItems($offset, $value);
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->collections[$offset]);
    }

    /**
     * @see \IteratorAggregate
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->collections);
    }

    public function count()
    {
        $count = 0;
        foreach ($this->collections as $collection) {
            $count += count($collection);
        }
        return $count;
    }
}