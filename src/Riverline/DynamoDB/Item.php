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
     * Return the Item attributes
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for the Item.
     * @param  array  $attributes
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $name => $value) {
            $this->setAttribute($name, $value);
        }
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
     * @return mixed|null
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

    /**
     * Return an Array representation of the item attributes
     * @return array
     */
    public function getArrayCopy()
    {
        $attributes = array();

        foreach($this->attributes as $key => $attribute) {
            /** @var Attribute $attribute->get */
            $attributes[$key] = $attribute->getValue();
        }

        return $attributes;
    }
}
