<?php

namespace Riverline\DynamoDB\Context;

use \Riverline\DynamoDB\AttributeCondition;

class Scan extends Collection
{
    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @param string $name
     * @param string $operator
     * @param mixed $value
     * @return \Riverline\DynamoDB\Context\Scan
     */
    public function addFilter($name, $operator, $value)
    {
        $this->filters[$name] = new AttributeCondition($operator, $value);

        return $this;
    }

    /**
     * @param bool $ConsistentRead
     * @throws \Riverline\DynamoDB\Exception\AttributesException
     */
    public function setConsistentRead($ConsistentRead)
    {
        throw new \Riverline\DynamoDB\Exception\AttributesException('Scan do not support consistent read');
    }

    /**
     * Return the context formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = parent::getForDynamoDB();

        foreach($this->filters as $name => $filter) {
            /* @var $filter AttributeCondition */
            $parameters['ScanFilter'][$name] = $filter->getForDynamoDB();
        }

        return $parameters;
    }
}