<?php

namespace Riverline\DynamoDB\Batch;

class GetRequestItemTest extends \PHPUnit_Framework_TestCase
{
    public function testPopulateFromDynamoDB()
    {
        $json = <<<JSON
{"Keys": 
    [{"HashKeyElement": {"S":"KeyValue1"}, "RangeKeyElement":{"N":"1"}},
    {"HashKeyElement": {"S":"KeyValue3"}, "RangeKeyElement":{"N":"2"}},
    {"HashKeyElement": {"S":"KeyValue5"}, "RangeKeyElement":{"N":"3"}}],
"AttributesToGet":["AttributeName1", "AttributeName2", "AttributeName3"]}
JSON;
        $data = json_decode($json);
        $getRequestItem = new GetRequestItem();
        $getRequestItem->populateFromDynamoDB($data);    
    }

}