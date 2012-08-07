<?php

namespace Riverline\DynamoDB\Table;

/**
 * @class
 */
class ProvisionedThroughput
{
    /**
     * The provisioned read capacity units.
     * @var integer
     */
    private $readCapacity;

    /**
     * The provisioned write capacity units.
     * @var integer
     */
    private $writeCapacity;

    /**
     * Date when the capacity was increased.
     * @var integer 
     */
    private $lastIncreaseDateTime;

    /**
     * Date when the capacity was decreased.
     * @var integer 
     */
    private $lastDecreaseDateTime;

    /**
     * @param integer $read The provisioned read capacity units.
     * @param integer $write The provisioned write capacity units.
     * @param string|null $lastIncreaseDateTime
     * @param string|null $lastDecreaseDateTime
     */
    public function __construct($read, $write, $lastIncreaseDateTime = null, $lastDecreaseDateTime = null)
    {
        $this->readCapacity         = $read;
        $this->writeCapacity        = $write;
        $this->lastIncreaseDateTime = $lastIncreaseDateTime;
        $this->lastDecreaseDateTime = $lastDecreaseDateTime;
    }

    /**
     * Return the provisioned read capacity units.
     * @return integer
     */
    public function getReadCapacity()
    {
        return $this->readCapacity;
    }

    /**
     * Return the provisioned write capacity units.
     * @return integer
     */
    public function getWriteCapacity()
    {
        return $this->writeCapacity;
    }

    /**
     * Return date when the capacity was increased.
     * @return integer
     */
    public function getLastIncreaseDateTime()
    {
        return $this->lastIncreaseDateTime;
    }

    /**
     * Return date when the capacity was decreased.
     * @return integer
     */
    public function getLastDecreaseDateTime()
    {
        return $this->lastDecreaseDateTime;
    }

    /**
     * Return provisioned throughput for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        return array(
            'ReadCapacityUnits'  => $this->getReadCapacity(),
            'WriteCapacityUnits' => $this->getWriteCapacity()
        );
    }
}