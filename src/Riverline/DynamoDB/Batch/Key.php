<?php

namespace Riverline\DynamoDB\Batch;

use Riverline\DynamoDB\Attribute;

/**
 * @class
 */
class Key
{
    /**
     * @var Attribute
     */
    private $hash;

    /**
     * @var Attribute
     */
    private $range;

    /**
     * @param Attribute|mixed $hash Hash type primary key
     * @param Attribute|mixed|null $range Range type primary key
     */
    public function __construct($hash, $range = null)
    {
        if (!is_null($hash)) {
            $this->hash = $this->populateAttribute($hash);
        }
        if (!is_null($range)) {
            $this->range = $this->populateAttribute($range);
        }
    }

    /**
     *  @param Attribute|mixed $value
     */
    protected function populateAttribute($value) {
        $attribute = null;
        if ($value instanceof Attribute) {
            $attribute = $value;  
        } else {
            $attribute = new Attribute($value);
        }
        return $attribute;
    }

    /**
     * @return Attribute
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return Attribute|null
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Return the key formated for DynamoDB
     * @return array
     */
    public function getForDynamoDB()
    {
        $parameters = array();
        $parameters['HashKeyElement'] = $this->hash->getForDynamoDB();
        if ($this->range) {
            $parameters['RangeKeyElement'] = $this->range->getForDynamoDB();
        }
        return $parameters;
    }

    public function populateFromDynamoDB(\stdClass $data)
    {
        if (isset($data->HashKeyElement)) {
            list ($type, $value) = each($data->HashKeyElement);
            $this->hash = new Attribute($value, $type);
        }
        if (isset($data->RangeKeyElement)) {
            list ($type, $value) = each($data->RangeKeyElement);
            $this->hash = new Attribute($value, $type);
        }
    }

}