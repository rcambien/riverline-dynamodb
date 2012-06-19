<?php

namespace Riverline\DynamoDB;

use Riverline\DynamoDB\Attribute;

/**
 * @class
 */
class UpdateAction
{
    /**
     * The update action
     * @var string. \AmazonDynamoDB::ACTION_ADD, \AmazonDynamoDB::ACTION_DELETE or \AmazonDynamoDB::ACTION_PUT.
     */
    protected $action;

    /**
     * The expected attribute value
     * @var \Riverline\DynamoDB\Attribute $value
     */
    protected $value;

    /**
     * @param string $action
     * @param boolean|\Riverline\DynamoDB\Attribute|mixed|null $value
     * @param string|null $type
     */
    public function __construct($action, $value = null, $type = null)
    {
        $this->action = $action;
        $this->value = null;
        
        if (null === $value) {
            $this->value = null;
        } else if ($value instanceof Attribute) {
            $this->value = $value;
        } else {
            $this->value = new Attribute($value, $type);
        }
    }

    /**
     * Return the update action
     * @return string
     */
    public function getAction()
    {
        return $this->action;
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
        $action = $this->getAction();
        $value = $this->getValue();

        $parameters['Action'] = $action;
        if (isset($value)) {
            $parameters['Value'] = $value->getForDynamoDB();
        }

        return $parameters;
    }
}