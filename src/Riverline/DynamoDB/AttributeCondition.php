<?php

namespace Riverline\DynamoDB;

use \Riverline\DynamoDB\Exception\AttributesException;

use Aws\DynamoDb\Enum\ComparisonOperator;

/**
 * @class
 */
class AttributeCondition
{
    /**
     * The condition operator
     * @var string
     */
    protected $operator;

    /**
     * The condition attributes
     * @var Attribute|array
     */
    protected $attributes;

    /**
     * @param string $operator The condition operator
     * @param Attribute|array $attributes The condition attributes
     * @throws Exception\AttributesException
     */
    public function __construct($operator, $attributes)
    {
        if (ComparisonOperator::BETWEEN === $operator) {
            if  (!is_array($attributes) || 2 !== count($attributes)) {
                throw new AttributesException('Between operator must have two range attributes.');
            } else {
                foreach($attributes as $attribute) {
                    $this->attributes[] = new Attribute($attribute);
                }
            }
        } else {
            $this->attributes = new Attribute($attributes);
        }

        $this->operator   = $operator;
    }

    /**
     * Return the condition operator
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Return the condition attributes
     * @return array|Attribute
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Return the condition formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $operator   = $this->getOperator();
        $attributes = $this->getAttributes();

        $condition = array(
            'ComparisonOperator' => $operator
        );

        if (ComparisonOperator::BETWEEN === $operator) {
            $rangeFrom = array_shift($attributes);
            $rangeTo   = array_shift($attributes);
            $condition['AttributeValueList'] = array(
                $rangeFrom->getForDynamoDB(),
                $rangeTo->getForDynamoDB()
            );
        } else {
            $condition['AttributeValueList'] = array(
                $attributes->getForDynamoDB()
            );
        }

        return $condition;
    }
}