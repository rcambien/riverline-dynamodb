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
     * Read and Write unit counter
     * @var int
     */
    protected $readUnit = array(), $writeUnit = array();

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
     * @param string|null $table If null, return consumed units for all tables
     * @return float
     */
    public function getConsumedReadUnits($table = null)
    {
        if (is_null($table)) {
            return array_sum($this->readUnit);
        } else {
            return (isset($this->readUnit[$table])?$this->readUnit[$table]:0);
        }
    }

    /**
     * Update the Read Units counter
     * @param string $table
     * @param float $units
     */
    protected function addConsumedReadUnits($table, $units)
    {
        if (isset($this->readUnit[$table])) {
            $this->readUnit[$table] += $units;
        } else {
            $this->readUnit[$table] = $units;
        }
    }

    /**
     * Return the number of write units consumed
     * @param string|null $table If null, return consumed units for all tables
     * @return float
     */
    public function getConsumedWriteUnits($table = null)
    {
        if (is_null($table)) {
            return array_sum($this->writeUnit);
        } else {
            return (isset($this->writeUnit[$table])?$this->writeUnit[$table]:0);
        }
    }

    /**
     * Update the Write Units counter
     * @param string $table
     * @param float $units
     */
    protected function addConsumedWriteUnits($table, $units)
    {
        if (isset($this->writeUnit[$table])) {
            $this->writeUnit[$table] += $units;
        } else {
            $this->writeUnit[$table] = $units;
        }
    }

    /**
     * Reset the read and write unit counter
     * @param string|null $table If null, reset all consumed units
     */
    public function resetConsumedUnits($table = null)
    {
        if (is_null($table)) {
            $this->readUnit  = array();
            $this->writeUnit = array();
        } else {
            unset(
                $this->readUnit[$table],
                $this->writeUnit[$table]
            );
        }
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
        $this->addConsumedWriteUnits($table, floatval($response->ConsumedCapacityUnits));

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
        $this->addConsumedWriteUnits($table, floatval($response->ConsumedCapacityUnits));

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

        $this->addConsumedReadUnits($table, floatval($response->ConsumedCapacityUnits));

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
            /** @var $attribute Attribute */
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
        $this->addConsumedWriteUnits($table, floatval($response->ConsumedCapacityUnits));

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

        $this->addConsumedReadUnits($table, floatval($response->ConsumedCapacityUnits));

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

        $this->addConsumedReadUnits($table, floatval($response->ConsumedCapacityUnits));

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
     * Get a batch of items
     * @param Context\BatchGet $context
     * @throws \Riverline\DynamoDB\Exception\AttributesException
     * @return \Riverline\DynamoDB\Batch\BatchCollection
     */
    public function batchGet(Context\BatchGet $context)
    {
        if (0 === count($context)) {
            throw new Exception\AttributesException("Context doesn't contain any key to get");
        }

        $parameters = $context->getForDynamoDB();

        $response = $this->parseResponse($this->connector->batch_get_item($parameters));

        // UnprocessedKeys
        if (count((array)$response->UnprocessedKeys)) {
            $unprocessKeyContext = new Context\BatchGet();
            foreach ($response->UnprocessedKeys as $table => $tableParameters) {
                foreach ($tableParameters->Keys as $key) {
                    $unprocessKeyContext->addKey($table, current($key->HashKeyElement), current($key->RangeKeyElement));
                }
                if (!empty($tableParameters->AttributesToGet)) {
                    $unprocessKeyContext->setAttributesToGet($table, $tableParameters->AttributesToGet);
                }
            }
        } else {
            $unprocessKeyContext = null;
        }

        $collection = new Batch\BatchCollection($unprocessKeyContext);

        foreach ($response->Responses as $table => $responseItems) {
            $this->addConsumedReadUnits($table, floatval($responseItems->ConsumedCapacityUnits));

            $items = new Collection();
            foreach ($responseItems->Items as $responseItem) {
                $item = new Item($table);
                $item->populateFromDynamoDB($responseItem);
                $items->add($item);
            }

            $collection->setItems($table, $items);
        }

        return $collection;
    }

    /**
     * Put Items and delete Keys by batch
     * @param Context\BatchWrite $context
     * @return null|Context\BatchWrite Return a new BatchWrite context if some request were not processed
     * @throws Exception\AttributesException
     */
    public function batchWrite(Context\BatchWrite $context)
    {
        if (0 === count($context)) {
            throw new Exception\AttributesException("Context doesn't contain anything to write");
        }

        $parameters = $context->getForDynamoDB();

        $response = $this->parseResponse($this->connector->batch_write_item($parameters));

        // UnprocessedKeys
        if (count((array)$response->UnprocessedItems)) {
            $newContext = new Context\BatchWrite();
            foreach ($response->UnprocessedItems as $table => $tableParameters) {
                foreach ($tableParameters as $request) {
                    if (isset($request->DeleteRequest)) {
                        $keys = $request->DeleteRequest->Key;
                        $newContext->addKeyToDelete(
                            $table,
                            current($keys->HashKeyElement),
                            (isset($keys->RangeKeyElement)?current($keys->RangeKeyElement):null)
                        );
                    } elseif (isset($request->PutRequest)) {
                        $item = new Item($table);
                        $item->populateFromDynamoDB($request->PutRequest->Item);
                        $newContext->addItemToPut($item);
                    }
                }
            }
        } else {
            $newContext = null;
        }

        // Write Unit
        foreach ($response->Responses as $table => $responseItems) {
            $this->addConsumedWriteUnits($table, floatval($responseItems->ConsumedCapacityUnits));
        }

        return $newContext;
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
        $this->parseResponse($this->connector->create_table($parameters));
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
        $this->parseResponse($this->connector->update_table($parameters));
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
        $this->parseResponse($this->connector->delete_table($parameters));
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
        $tableDescription = new Table\TableDescription();
        $tableDescription->populateFromDynamoDB($response->Table);
        return $tableDescription;
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

    public function waitForTableToBeInState($table, $status, $sleep = 3, $max = 20)
    {
        $current = 0;
        do {
            $tableDescription = $this->describeTable($table);
            if ($status == $tableDescription->getTableStatus()) {
                return $tableDescription;
            } else {
                sleep($sleep);
            }
        } while(++$current < $max);

        throw new \Exception('waitForTableToBeInState timeout');
    }

    /**
     * Parse the SDK response to detect error
     * @param \CFResponse $response The raw SDK response
     * @return \stdClass The response body
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

            if (isset($body->__type)) {
                list ($api, $type) = explode('#', $body->__type);
            } else {
                $type = '';
            }

            switch ($type) {
                case 'ProvisionedThroughputExceededException':
                    throw new Exception\ProvisionedThroughputExceededException($message);
                default:
                    throw new Exception\ServerException($message);
            }
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
}