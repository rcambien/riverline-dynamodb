<?php

namespace Riverline\DynamoDB\Context;

use \Riverline\DynamoDB\AttributeCondition;

class Scan extends Collection
{
    protected $filters = array();

    public function addFilter($name, $operator, $value)
    {
        $this->filters[$name] = new AttributeCondition($operator, $value);

        return $this;
    }

    public function setConsistentRead($ConsistentRead)
    {
        throw new \Riverline\DynamoDB\Exception\AttributesException('Scan do not support consistent read');
    }

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