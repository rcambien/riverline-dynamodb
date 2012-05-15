<?php

namespace Riverline\DynamoDB;

/**
 * @class
 */
class Item implements \ArrayAccess, \IteratorAggregate
{
    /**
     * The item table name
     * @var string
     */
    private $table;

    /**
     * The item attributes
     * @var array
     */
    private $attributes = array();

    /**
     * @param $table string The item table name
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Return the Item table name
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $name
     * @param \Riverline\DynamoDB\Attribute|mixed $value
     * @param null|string $type
     */
    public function setAttribute($name, $value, $type = null)
    {
        if ($value instanceof Attribute) {
            $this->attributes[$name] = $value;
        } else {
            $this->attributes[$name] = new Attribute($value, $type);
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
        return (isset($this->attributes[$offset])?$this->attributes[$offset]->getValue():null);
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

    /**
     * Populate attributes from the raw DynamoDB response
     * @param \CFSimpleXML $xml
     */
    public function populateFromDynamoDB(\CFSimpleXML $xml)
    {
        foreach ($xml as $name => $value) {
            list ($type, $value) = each($value);
            $this->setAttribute($name, $value, $type);
        }
    }
}
