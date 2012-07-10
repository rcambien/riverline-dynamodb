<?php

namespace Riverline\DynamoDB\Batch;

use Riverline\DynamoDB\Item;

/**
 * @class
 */
class WriteRequestItem
{
    /**
     * @var array
     */
    private $putItems;
    
    /**
     * @var array
     */
    private $deleteKeys;

    public function __construct()
    {
        $this->putItems = array();
        $this->deleteKeys = array();
    }

    /**
     * Set items to put
     * @var array $items
     */
    public function put($items)
    {
        if (!is_array($items)) {
            $items = array($items);
        }
        foreach ($items as $item) {
            $this->addPutItem($item);
        }
    }

    /**
     * Add an item to put
     * @var Item $item
     */
    public function addPutItem(Item $item)
    {
        $this->putItems[] = $item;
    }

    /**
     * Set keys to delete
     * @var array $keys
     */
    public function delete($keys)
    {
        if (!is_array($keys)) {
            $keys = array($keys);
        }
        foreach ($keys as $key) {
            $this->addDeleteKey($key);
        }
    }

    /**
     * Add a key to delete
     * @var Key|mixed $value Key value.
     */
    public function addDeleteKey($value)
    {
        $key = null;
        if ($value instanceof Key) {
            $key = $value;
        } else if (is_array($value)) {
            if (1 < count($value)) {
                $key = new Key($value[0], $value[1]);
            } else {
                $key = new Key($value[0]);
            }
        }
        $this->deleteKeys[] = $key;
    }

    /**
     * Return if the put/delete requests are set.
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->putItems) && empty($this->deleteKeys);
    }

    /**
     * Return a write request item formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();
        if (!empty($this->putItems)) {
            foreach ($this->putItems as $item) {
                $request = array();
                $request['PutRequest']['Item'] = $item->getForDynamoDB();
                $parameters[] = $request;
            }
        }
        if (!empty($this->deleteKeys)) {
            foreach ($this->deleteKeys as $key) {
                $request = array();
                $request['DeleteRequest']['Key'] = $key->getForDynamoDB();
                $parameters[] = $request;
            }
        }
        return $parameters;
    }

    /**
     * Populate a write request item from the raw DynamoDB response
     * @param array $data
     */
    public function populateFromDynamoDB($table, array $data)
    {
        foreach($data as $request) {
            if (isset($request->PutRequest)) {
                $item = new Item($table);
                $item->populateFromDynamoDB($request->PutRequest->Item);
                $this->addPutItem($item);
            }
            if (isset($request->DeleteRequest)) {
                $key = new Key(null);
                $key->populateFromDynamoDB($request->DeleteRequest->Key);
                $this->addDeleteKey($key);
            }
        }
    }
}