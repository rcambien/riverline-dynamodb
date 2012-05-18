<?php

namespace Riverline\DynamoDB\Context;

use \Riverline\DynamoDB\AttributeCondition;

class Query extends Collection
{
    protected $rangeCondition;

    protected $ScanIndexForward;

    public function setRangeCondition($operator, $attributes)
    {
        $this->rangeCondition = new AttributeCondition($operator, $attributes);

        return $this;
    }

    static public function create($operator, $attributes)
    {
        $query = new Query();
        $query->setRangeCondition($operator, $attributes);

        return $query;
    }

    public function setScanIndexForward($ScanIndexForward)
    {
        $this->ScanIndexForward = (bool)$ScanIndexForward;
    }

    public function getForDynamoDB()
    {
        $parameters = parent::getForDynamoDB();

        $rangeCondition = $this->rangeCondition;
        if (null !== $rangeCondition) {
            $parameters['RangeKeyCondition'] = $rangeCondition->getForDynamoDB();
        }

        if (null !== $this->ScanIndexForward) {
            $parameters['ScanIndexForward'] = $this->ScanIndexForward;
        }

        return $parameters;
    }
}