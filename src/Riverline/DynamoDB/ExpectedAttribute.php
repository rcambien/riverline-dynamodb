<?php

namespace Riverline\DynamoDB;

use Riverline\DynamoDB\Attribute;

/**
 * @class
 */
class ExpectedAttribute
{
    /**
     * Whether expected attribute exists or not
     * @var boolean
     */
    protected $exists;

    /**
     * The expected attribute value
     * @var \Riverline\DynamoDB\Attribute $value
     */
    protected $value;

    /**
     * @param boolean|\Riverline\DynamoDB\Attribute|mixed|null $value
     * @param string|null $type
     */
    public function __construct($value = null, $type = null)
    {
        $this->exists = null;
        $this->value = null;
        
        if (is_bool($value)) {
            $this->exists = $value;
        } else if ($value instanceof Attribute) {
            $this->value = $value;
        } else {
            $this->value = new Attribute($value, $type);
        }
    }

    /**
     * Return whether expected attribute exists or not
     * @return boolean
     */
    public function getExists()
    {
        return $this->exists;
    }

    /**
     * Return the expected attribute value
     * @return \Riverline\DynamoDB\Attribute $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return the condition formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $exists = $this->getExists();
        $value = $this->getValue();

        $condition = array();
        if (isset($exists)) {
           $condition['Exists'] = $exists;
        }
        if (isset($value)) {
            $condition['Value'] = $value->getForDynamoDB();
        }

        return $condition;
    }
}