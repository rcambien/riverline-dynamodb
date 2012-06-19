<?php

namespace Riverline\DynamoDB;

/**
 * @class
 */
class Expected implements \ArrayAccess, \IteratorAggregate
{
    /**
     * The expected attributes
     * @var array
     */
    private $attributes = array();

    /**
     * @param string $name
     * @param \Riverline\DynamoDB\ExpectedAttribute|mixed $value
     * @param null|string $type
     */
    public function setAttribute($name, $value, $type = null)
    {
        if ($value instanceof ExpectedAttribute) {
            $this->attributes[$name] = $value;
        } else {
            $this->attributes[$name] = new ExpectedAttribute($value, $type);
        }
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return (isset($this->attributes[$offset])?$this->attributes[$offset]:null);
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     * @param $value
     * @throws Exception\AttributesException
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            throw new Exception\AttributesException('Square bracket syntax isn\'t allowed');
        }

        $this->setAttribute($offset, $value);
    }

    /**
     * @see \ArrayAccess
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * @see \IteratorAggregate
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->attributes);
    }
}