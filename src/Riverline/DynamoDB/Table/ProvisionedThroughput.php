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
    private $read;

    /**
     * The provisioned write capacity units.
     * @var integer
     */
    private $write;

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
     */
    public function __construct($read, $write, $lastIncreaseDateTime = null, $lastDecreaseDateTime = null)
    {
        $this->read = $read;
        $this->write = $write;
        $this->lastIncreaseDateTime = $lastIncreaseDateTime;
        $this->lastDecreaseDateTime = $lastDecreaseDateTime;
    }

    /**
     * Return the provisioned read capacity units.
     * @return integer
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Return the provisioned write capacity units.
     * @return integer
     */
    public function getWrite()
    {
        return $this->write;
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
            'ReadCapacityUnits' => $this->getRead(),
            'WriteCapacityUnits' => $this->getWrite()
            );
    }

    /**
     * Populate provisioned throughput from the raw DynamoDB response
     * @param \stdClass $data
     */
    public function populateFromDynamoDB(\stdClass $data)
    {
        $this->read = $data->ReadCapacityUnits;
        $this->write = $data->WriteCapacityUnits;
        $this->lastIncreaseDateTime = isset($data->LastIncreaseDateTime)?$data->LastIncreaseDateTime:null;
        $this->lastDecreaseDateTime = isset($data->LastDecreaseDateTime)?$data->LastDecreaseDateTime:null;
    }
}