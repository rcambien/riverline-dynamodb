<?php

namespace Riverline\DynamoDB;

/**
 * @class
 */
class Connection
{
    /**
     * @var \AmazonDynamoDB
     */
    protected $connector;

    /**
     * @var int
     */
    protected $readUnit = 0, $writeUnit = 0;

    /**
     * @param string $key The AWS access Key
     * @param string $secret The AWS secret Key
     * @param string $cacheConfig The DynamoDB SDK cache configuration
     * @param string|null $region The DynamoDB region endpoint
     * @throws \Exception
     */
    public function __construct($key, $secret, $cacheConfig, $region = null)
    {
        if (!class_exists('\AmazonDynamoDB')) {
            throw new \Exception('Missing AWS PHP SDK');
        }

        $this->connector = new \AmazonDynamoDB(array(
            'key'    => $key,
            'secret' => $secret,
            'default_cache_config' => $cacheConfig,
        ));

        if (null !== $region) {
            $this->connector->set_region($region);
        }

        // Raw JSON response
        $this->connector->parse_the_response = false;
    }

    /**
     * Return the DynamoDB object
     * @return \AmazonDynamoDB
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * Return the number of read units consumed
     * @return int
     */
    public function getConsumedReadUnits()
    {
        return $this->readUnit;
    }

    /**
     * Return the number of write units consumed
     * @return int
     */
    public function getConsumedWriteUnits()
    {
        return $this->writeUnit;
    }

    /**
     * Reset the read and write unit counter
     */
    public function resetConsumedUnits()
    {
        $this->readUnit  = 0;
        $this->writeUnit = 0;
    }

    /**
     * Add an item to DynamoDB via the put_item call
     * @param Item $item
     * @param Context\Put|null $context The call context
     * @return array|null
     * @throws Exception\AttributesException
     */
    public function put(Item $item, Context\Put $context = null)
    {
        $table = $item->getTable();

        if (empty($table)) {
            throw new \Riverline\DynamoDB\Exception\AttributesException('Item do not have table defined');
        }

        $attributes = array();
        foreach ($item as $name => $attribute) {
            /** @var $attribute \Riverline\DynamoDB\Attribute */
            if ("" !== $attribute->getValue()) {
                // Only not empty string
                $attributes[$name] = $attribute->getForDynamoDB();
            }
        }
        $parameters = array(
            'TableName' => $table,
            'Item'      => $attributes,
        );

        if (null !== $context) {
            $parameters += $context->getForDynamoDB();
        }

        $response = $this->parseResponse($this->connector->put_item($parameters));

        // Update write counter
        $this->writeUnit += floatval($response->ConsumedCapacityUnits);

        return $this->populateAttributes($response);
    }

    /**
     * Delete an item via the delete_item call
     * @param string $table The item table
     * @param mixed $hash The primary hash key
     * @param mixed|null $range The primary range key
     * @param Context\Delete|null $context The call context
     * @return array|null
     */
    public function delete($table, $hash, $range = null, Context\Delete $context = null)
    {
        // Primary key
        $hash = new Attribute($hash);
        $key = array(
            'HashKeyElement' => $hash->getForDynamoDB()
        );

        // Range key
        if (null !== $range) {
            $range = new Attribute($range);
            $key['RangeKeyElement'] = $range->getForDynamoDB();
        }

        $parameters = array(
            'TableName' => $table,
            'Key'       => $key
        );

        if (null !== $context) {
            $parameters += $context->getForDynamoDB();
        }

        $response = $this->parseResponse($this->connector->delete_item($parameters));

        // Update write counter
        $this->writeUnit += floatval($response->ConsumedCapacityUnits);

        return $this->populateAttributes($response);
    }

    /**
     * Get an item via the get_item call
     * @param string $table The item table
     * @param mixed $hash The primary hash key
     * @param mixed|null $range The primary range key
     * @param Context\Get|null $context The call context
     * @return Item|null
     */
    public function get($table, $hash, $range = null, Context\Get $context = null)
    {
        if (null === $context) {
            $context = new Context\Get();
        }

        // Primary key
        $hash = new Attribute($hash);
        $parameters = array(
            'TableName' => $table,
            'Key'       => array(
                'HashKeyElement' => $hash->getForDynamoDB()
            )
        ) + $context->getForDynamoDB();

        // Range key
        if (null !== $range) {
            $range = new Attribute($range);
            $parameters['Key']['RangeKeyElement'] = $range->getForDynamoDB();
        }

        $response = $this->parseResponse($this->connector->get_item($parameters));

        $this->readUnit += floatval($response->ConsumedCapacityUnits);

        if (isset($response->Item)) {
            $item = new Item($table);
            $item->populateFromDynamoDB($response->Item);
            return $item;
        } else {
            return null;
        }
    }

    /**
     * Update an item via the update_item call
     * @param string $table The item table
     * @param mixed $hash The primary hash key
     * @param mixed|null $range The primary range key
     * @param AttributeUpdate $update
     * @param Context\Update|null $context The call context
     * @return array|null
     * @throws Exception\AttributesException
     */
    public function update($table, $hash, $range = null, AttributeUpdate $update, Context\Update $context = null)
    {
        // Primary key
        $hash = new Attribute($hash);
        $key = array(
            'HashKeyElement' => $hash->getForDynamoDB()
        );

        // Range key
        if (null !== $range) {
            $range = new Attribute($range);
            $key['RangeKeyElement'] = $range->getForDynamoDB();
        }

        $attributes = array();
        foreach ($update as $name => $attribute) {
            $attributes[$name] = $attribute->getForDynamoDB();
        }
        
        $parameters = array(
            'TableName'         => $table,
            'Key'               => $key,
            'AttributeUpdates'  => $attributes,
        );

        if (null !== $context) {
            $parameters += $context->getForDynamoDB();
        }

        $response = $this->parseResponse($this->connector->update_item($parameters));

        // Update write counter
        $this->writeUnit += floatval($response->ConsumedCapacityUnits);

        return $this->populateAttributes($response);
    }

    /**
     * Get items via the query call
     * @param string $table The item table
     * @param mixed $hash The primary hash key
     * @param Context\Query|null $context The call context
     * @return Collection
     */
    public function query($table, $hash, Context\Query $context = null)
    {
        if (null === $context) {
            $context = new Context\Query();
        }

        $hash = new Attribute($hash);
        $parameters = array(
            'TableName'    => $table,
            'HashKeyValue' => $hash->getForDynamoDB(),
        ) + $context->getForDynamoDB();

        $response = $this->parseResponse($this->connector->query($parameters));

        $this->readUnit += floatval($response->ConsumedCapacityUnits);

        $items = new Collection((isset($response->LastEvaluatedKey)?$response->LastEvaluatedKey:null));
        if (!empty($response->Items)) {
            foreach ($response->Items as $responseItem) {
                $item = new Item($table);
                $item->populateFromDynamoDB($responseItem);
                $items->add($item);
            }
        }
        return $items;
    }

    /**
     * Get items via the scan call
     * @param string $table The item table
     * @param Context\Scan|null $context The call context
     * @return Collection
     */
    public function scan($table, Context\Scan $context = null)
    {
        if (null === $context) {
            $context = new Context\Scan();
        }

        $parameters = array(
            'TableName' => $table
        ) + $context->getForDynamoDB();

        $response = $this->parseResponse($this->connector->scan($parameters));

        $this->readUnit += floatval($response->ConsumedCapacityUnits);

        $items = new Collection((isset($response->LastEvaluatedKey)?$response->LastEvaluatedKey:null));
        if (!empty($response->Items)) {
            foreach ($response->Items as $responseItem) {
                $item = new Item($table);
                $item->populateFromDynamoDB($responseItem);
                $items->add($item);
            }
        }
        return $items;
    }

    /**
     * Create table via the create_table call
     * @param string $table The name of the table
     * @param Table\KeySchema $keySchama
     * @param Table\ProvisionedThroughput $provisionedThroughput
     * @return Table\TableDescription
     */
    public function createTable($table, Table\KeySchema $keySchama, Table\ProvisionedThroughput $provisionedThroughput)
    {
        $parameters = array(
            'TableName' => $table,
            'KeySchema' => $keySchama->getForDynamoDB(),
            'ProvisionedThroughput' => $provisionedThroughput->getForDynamoDB()
        );
        $response = $this->parseResponse($this->connector->create_table($parameters));
        return $this->populateTableDescription($response);
    }

    /**
     * Update table via the update_table call
     * @param string $table The name of the table
     * @param Table\ProvisionedThroughput $provisionedThroughput
     * @return Table\TableDescription
     */
    public function updateTable($table, Table\ProvisionedThroughput $provisionedThroughput)
    {
        $parameters = array(
            'TableName' => $table,
            'ProvisionedThroughput' => $provisionedThroughput->getForDynamoDB()
        );
        $response = $this->parseResponse($this->connector->update_table($parameters));
        return $this->populateTableDescription($response);
    }

    /**
     * Delete table via the delete_table call
     * @param string $table The name of the table
     * @return Table\TableDescription
     */
    public function deleteTable($table)
    {
        $parameters = array(
            'TableName' => $table,
        );
        $response = $this->parseResponse($this->connector->delete_table($parameters));
        return $this->populateTableDescription($response);
    }

    /**
     * Describe table via the describe_table call
     * @param string $table The name of the table
     * @return Table\TableDescription
     */
    public function describeTable($table)
    {
        $parameters = array(
            'TableName' => $table,
        );
        $response = $this->parseResponse($this->connector->describe_table($parameters));
        return $this->populateTableDescription($response);
    }

    /**
     * List tables via the list_tables call
     * @param integer $limit
     * @param string $exclusiveStartTableName
     * @return Table\TableCollection
     */
    public function listTables($limit = null, $exclusiveStartTableName = null)
    {
        $parameters = array();
        if (null !== $limit) {
            $parameters['Limit'] = $limit;
        }
        if (null !== $exclusiveStartTableName) {
            $parameters['ExclusiveStartTableName'] = $exclusiveStartTableName;
        }
        $response = $this->parseResponse($this->connector->list_tables($parameters));

        $tables = new Table\TableCollection((isset($response->LastEvaluatedTableName)?$response->LastEvaluatedTableName:null));
        if (!empty($response->TableNames)) {
            foreach ($response->TableNames as $table) {
                $tables->add($table);
            }
        }
        return $tables;
    }

    /**
     * Parse the SDK response to detect error
     * @param \CFResponse $response The raw SDK response
     * @return \CFSimpleXML The response body
     * @throws Exception\ServerException
     */
    protected function parseResponse(\CFResponse $response)
    {
        $body = json_decode($response->body);
        if ($response->isOk()) {
            return $body;
        } else {
            $message = '';
            if (isset($body->message)) {
                $message = $body->message;
            } elseif (isset($body->Message)) {
                $message = $body->Message;
            }
            throw new Exception\ServerException($message);
        }
    }

    /**
     * Extract the attributes array from response data
     * @param \stdClass $data The response body data
     * @return array|null
     */
    protected function populateAttributes(\stdClass $data)
    {
        if (isset($data->Attributes)) {
            $attributes = array();
            foreach ($data->Attributes as $name => $value) {
                list ($type, $value) = each($value);
                $attributes[$name] = new Attribute($value, $type);
            }
            return $attributes;
        } else {
            return null;
        }
    }

    /**
     * Generate TableDescription from response data
     * @param \stdClass $data The response body data
     * @return Table\TableDescription
     */
    protected function populateTableDescription(\stdClass $data)
    {
        $tableDescription = new Table\TableDescription(null, null, null, null, null);
        if (isset($data->TableDescription)) {
            $tableDescription->populateFromDynamoDB($data->TableDescription);
        } else if (isset($data->Table)) {
            $tableDescription->populateFromDynamoDB($data->Table);
        }
        return $tableDescription;
    }
}