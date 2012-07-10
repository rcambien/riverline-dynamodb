<?php

namespace Riverline\DynamoDB\Table;

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
     * @param string $tableName The name of the table.
     * @param KeySchema $keySchema The primary key structure of the table.
     * @param ProvisionedThroughput $provisionedThroughput The provisioned throughput.
     * @param number $creationDateTime Date when the table was created.
     * @param string $tableStatus The current status of the table.
     * @param integer $itemCount Number of items in the table.
     * @param integer $tableSizeBytes Total size of the table in bytes.
     */
    public function __construct($tableName, $keySchema, $provisionedThroughput, $creationDateTime, $tableStatus, $itemCount = null, $tableSizeBytes = null)
    {
        $this->tableName = $tableName;
        $this->keySchema = $keySchema;
        $this->provisionedThroughput = $provisionedThroughput;
        $this->creationDateTime = $creationDateTime;
        $this->tableStatus = $tableStatus;
        $this->itemCount = $itemCount;
        $this->tableSizeBytes = $tableSizeBytes;
    }

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
        $this->creationDateTime = isset($data->CreationDateTime)?$data->CreationDateTime:null;
        if (isset($data->KeySchema)) {
            $keySchema = new KeySchema(null, null);
            $keySchema->populateFromDynamoDB($data->KeySchema);
            $this->keySchema = $keySchema;
        }
        $provisionedThroughput = new ProvisionedThroughput(null, null);
        $provisionedThroughput->populateFromDynamoDB($data->ProvisionedThroughput);
        $this->provisionedThroughput = $provisionedThroughput;
        $this->tableName = $data->TableName;
        $this->tableStatus = $data->TableStatus;
        $this->itemCount = isset($data->ItemCount)?$data->ItemCount:null;
        $this->tableSizeBytes = isset($data->TableSizeBytes)?$data->TableSizeBytes:null;
    }
}