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

        if ($response->Item) {
            $item = new Item($table);
            $item->populateFromDynamoDB($response->Item[0]);
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

        $items = new Collection($response->LastEvaluatedKey);
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

        $items = new Collection($response->LastEvaluatedKey);
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
     * Parse the SDK response to detect error
     * @param \CFResponse $response The raw SDK response
     * @return \CFSimpleXML The response body
     * @throws Exception\ServerException
     */
    protected function parseResponse(\CFResponse $response)
    {
        if ($response->isOk()) {
            return $response->body;
        } else {
            $message = '';
            if (isset($response->body->message)) {
                $message = strval($response->body->message);
            } elseif (isset($response->body->Message)) {
                $message = strval($response->body->Message);
            }
            throw new Exception\ServerException($message);
        }
    }

    /**
     * Extract the attributes array from response XML
     * @param \CFSimpleXML $responseXml The response body XML
     */
    protected function populateAttributes(\CFSimpleXML $responseXml)
    {
        if ($responseXml->Attributes) {
            $attributes = array();
            foreach ($responseXml->Attributes[0] as $name => $value) {
                list ($type, $value) = each($value);
                $attributes[$name] = new Attribute($value, $type);
            }
            return $attributes;
        } else {
            return null;
        }
    }
}