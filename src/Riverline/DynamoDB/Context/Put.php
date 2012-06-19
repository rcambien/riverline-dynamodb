<?php

namespace Riverline\DynamoDB\Context;

use \Riverline\DynamoDB\ExpectedCondition;

/**
 * @class
 */
class Put
{
    /**
     * Expected values.
     * @var array
     */
    protected $expected;

    /**
     * A return constant.
     * \AmazonDynamoDB::RETURN_NONE or \AmazonDynamoDB::RETURN_ALL_OLD for put and delete operations.
     * \AmazonDynamoDB::RETURN_NONE, \AmazonDynamoDB::RETURN_ALL_OLD, \AmazonDynamoDB::RETURN_ALL_NEW, \AmazonDynamoDB::RETURN_UPDATED_OLD, \AmazonDynamoDB::RETURN_UPDATED_NEW for update operation.
     * @var string AmazonDynamoDB return constant. 
     */
    protected $returnValues;

    /**
     * @param \Riverline\DynamoDB\Expected $expected
     */
    public function setExpected(\Riverline\DynamoDB\Expected $expected)
    {
        $this->expected = $expected;

        return $this;
    }

    /**
     * @param string $returnValues AmazonDynamoDB return constant
     * @return \Riverline\DynamoDB\Context\Put
     */
    public function setReturnValues($returnValues)
    {
        $this->returnValues = $returnValues;

        return $this;
    }

    /**
     * Return the context formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();

        $expected = $this->expected;
        if (null !== $expected) {
            $expectedParameters = array();
            foreach ($expected as $name => $attribute) {
                $expectedParameters[$name] = $attribute->getForDynamoDB();
            }
            $parameters['Expected'] = $expectedParameters;
        }

        $returnValues = $this->returnValues;
        if (null !== $returnValues) {
            $parameters['ReturnValues'] = $returnValues;
        }
        
        return $parameters;
    }
}