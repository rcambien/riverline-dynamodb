<?php

namespace Riverline\DynamoDB\Table;

/**
 * @class
 */
class KeySchemaElement
{
    /**
     * The attribute name
     * @var string
     */
    private $name;

    /**
     * The attribute type
     * @var string
     */
    private $type;

    /**
     * @param string $name The attribute name
     * @param string $type The attribute type
     */
    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Return the attribute name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the attribute type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return key schema element formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        return array(
            'AttributeName' => $this->getName(),
            'AttributeType' => $this->getType()
            );
    }

    /**
     * Populate key schema element from the raw DynamoDB response
     * @param \stdClass $data
     */
    public function populateFromDynamoDB(\stdClass $data)
    {
        $this->name = $data->AttributeName;
        $this->type = $data->AttributeType;
    }
}