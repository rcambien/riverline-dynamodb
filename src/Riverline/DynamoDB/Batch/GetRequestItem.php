<?php

namespace Riverline\DynamoDB\Batch;

/**
 * @class
 */
class GetRequestItem
{
    /**
     * @var array
     */
    private $keys;
    
    /**
     * @var array
     */
    private $attributesToGet;

    public function __construct()
    {
        $this->keys = array();
        $this->attributesToGet = null;
    }

    /**
     * Set batch read conditions.
     * @var array $keys
     * @var array $attributesToGet
     */
    public function get($keys, $attributesToGet = null)
    {
        foreach ($keys as $key) {
            $this->addKey($key);
        }
        $this->attributesToGet = $attributesToGet;
    }

    /**
     * Add a key for batch read.
     * @var Key|array $value
     */
    public function addKey($value)
    {
        $key = null;
        if ($value instanceof Key) {
            $key = $value;
        } else {
            if (!is_array($value)) {
                $value = array($value);
            }
            if (1 < count($value)) {
                $key = new Key($value[0], $value[1]);
            } else {
                $key = new Key($value[0]);
            }
        }
        $this->keys[] = $key;
    }

    /**
     * Return if the requested key is set.
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->keys);
    }

    /**
     * Return a get request item formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();
        $parameters['Keys'] = array();
        foreach ($this->keys as $key) {
            $parameters['Keys'][] = $key->getForDynamoDB();
        }
        if ($this->attributesToGet) {
            $parameters['AttributesToGet'] = $this->attributesToGet;
        }
        return $parameters;
    }

    /**
     * Populate a get request item from the raw DynamoDB response
     * @param \stdClass $data
     */
    public function populateFromDynamoDB(\stdClass $data)
    {
        $keys = array();
        foreach($data->Keys as $keyValue) {
            $key = new Key(null);
            $key->populateFromDynamoDB($keyValue);
            $keys[] = $key;
        }
        $attributesToGet = $data->AttributesToGet;
        $this->get($keys, $attributesToGet);
    }
}