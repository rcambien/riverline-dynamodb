<?php

namespace Riverline\DynamoDB;

/**
 * @class
 */
class Attribute implements \IteratorAggregate
{
    /**
     * The attribute type
     * @var string
     */
    private $type;

    /**
     * The attribute value
     * @var mixed
     */
    private $value;

    /**
     * @param string $value The attribute value
     * @param string|null $type The attribute type
     * @throws Exception\AttributesException
     */
    public function __construct($value, $type = null)
    {
        if (is_null($type)) {
            $type = $this->autoDetectType($value);
        }

        // Normalize
        switch ($type) {
            case \AmazonDynamoDB::TYPE_STRING:
                $value = strval($value);
                break;
            case \AmazonDynamoDB::TYPE_NUMBER:
                $value = $value+0;
                break;
            case \AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS:
                $value = (array) $value;
                $value = array_map(function ($value) { return strval($value);}, $value['SS']);
                sort($value);
                break;
            case \AmazonDynamoDB::TYPE_ARRAY_OF_NUMBERS:
                $value = (array) $value;
                $value = array_map(function ($value) { return $value+0;}, $value['NS']);
                sort($value);
                break;
            default:
                throw new \Riverline\DynamoDB\Exception\AttributesException('Invalid type '.$type);
        }

        $this->value = $value;
        $this->type  = $type;

    }

    /**
     * Return the attribute value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return the attribute value
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return true if this attribute is a collection of attributes
     * @return bool
     */
    public function isArray()
    {
        return (\AmazonDynamoDB::TYPE_ARRAY_OF_NUMBERS === $this->type
            || \AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS === $this->type
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->isArray()) {
            return join(',', $this->value);
        } else {
            return strval($this->value);
        }
    }

    /**
     * @see \IteratorAggregate
     * @return \ArrayIterator
     * @throws Exception\AttributesException
     */
    public function getIterator()
    {
        if ($this->isArray()) {
            return new \ArrayIterator($this->value);
        } else {
            throw new \Riverline\DynamoDB\Exception\AttributesException('This attribute is not an array of attributes');
        }
    }

    /**
     * Return attribute formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        if ($this->isArray()) {
            $value = array_map(function ($val) { return strval($val); }, $this->getValue());
        } else {
            $value = strval($this->getValue());
        }

        return array($this->getType() => $value);
    }

    /**
     * Auto detect attribute type base on value type
     * @todo Use the internal SDK method attribute()
     * @param mixed $value The attribute value
     * @return string The detected type
     */
    private function autoDetectType($value)
    {
        if (is_array($value)) {
            foreach ($value as $val) {
                if (!is_numeric($val)) {
                    return \AmazonDynamoDB::TYPE_ARRAY_OF_STRINGS;
                }
            }
            return \AmazonDynamoDB::TYPE_ARRAY_OF_NUMBERS;
        } elseif (is_numeric($value)) {
            return \AmazonDynamoDB::TYPE_NUMBER;
        } else {
            return \AmazonDynamoDB::TYPE_STRING;
        }
    }
}
