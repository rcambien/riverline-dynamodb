<?php

namespace Riverline\DynamoDB\Table;

use \Riverline\DynamoDB\Attribute;

/**
 * @class
 */
class TableDescription
{
    /**
     * Date when the table was created.
     * @var number
     */
    protected $creationDateTime;

    /**
     * The primary key structure of the table.
     * @var KeySchema
     */
    protected $keySchema;

    /**
     * The provisioned throughput.
     * @var ProvisionedThroughput
     */
    protected $provisionedThroughput;

    /**
     * The name of the table.
     * @var string
     */
    protected $tableName;

    /**
     * The current status of the table.
     * @var string
     */
    protected $tableStatus;

    /**
     * Number of items in the table.
     * @var integer
     */
    protected $itemCount;

    /**
     * Total size of the table in bytes.
     * @var integer
     */
    protected $tableSizeBytes;

    /**
     * Return date when the table was created.
     * @return number
     */
    public function getCreationDateTime()
    {
        return $this->creationDateTime;
    }

    /**
     * Return the primary key structure of the table.
     * @return KeySchema
     */
    public function getKeySchema()
    {
        return $this->keySchema;
    }

    /**
     * Return the provisioned throughput.
     * @return ProvisionedThroughput
     */
    public function getProvisionedThroughput()
    {
        return $this->provisionedThroughput;
    }

    /**
     * Return the name of the table.
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Return the current status of the table.
     * @return string
     */
    public function getTableStatus()
    {
        return $this->tableStatus;
    }

    /**
     * Return number of items in the table.
     * @return integer
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * Return total size of the table in bytes.
     * @return integer
     */
    public function getTableSizeBytes()
    {
        return $this->tableSizeBytes;
    }

    /**
     * Populate table description from the raw DynamoDB response
     * @param \stdClass $data
     */
    public function populateFromDynamoDB(\stdClass $data)
    {
        $this->tableName        = $data->TableName;
        $this->tableStatus      = $data->TableStatus;
        $this->creationDateTime = $data->CreationDateTime;

        $this->itemCount        = (isset($data->ItemCount)?$data->ItemCount:0);
        $this->tableSizeBytes   = (isset($data->TableSizeBytes)?$data->TableSizeBytes:0);

        $keySchema = $data->KeySchema;
        $hash = new KeySchemaElement(
            $keySchema->HashKeyElement->AttributeName,
            $keySchema->HashKeyElement->AttributeType
        );
        if (isset($keySchema->RangeKeyElement)) {
            $range = new KeySchemaElement(
                $keySchema->RangeKeyElement->AttributeName,
                $keySchema->RangeKeyElement->AttributeType
            );
        } else {
            $range = null;
        }

        $this->keySchema = new KeySchema($hash, $range);

        $provisionedThroughput = $data->ProvisionedThroughput;
        $this->provisionedThroughput = new ProvisionedThroughput(
            $provisionedThroughput->ReadCapacityUnits,
            $provisionedThroughput->WriteCapacityUnits,
            (isset($provisionedThroughput->LastIncreaseDateTime)?$provisionedThroughput->LastIncreaseDateTime:null),
            (isset($provisionedThroughput->LastDecreaseDateTime)?$provisionedThroughput->LastDecreaseDateTime:null)
        );
    }
}