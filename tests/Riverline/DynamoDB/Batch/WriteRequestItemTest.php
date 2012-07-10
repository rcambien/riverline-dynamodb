<?php

namespace Riverline\DynamoDB\Batch;

class WriteRequestItemTest extends \PHPUnit_Framework_TestCase
{
    public function testPopulateFromDynamoDB()
    {
                $json = <<<JSON
[
  {
    "PutRequest":{
      "Item":{
        "ReplyDateTime":{
          "S":"2012-04-03T11:04:47.034Z"
        },
        "Id":{
          "S":"Amazon DynamoDB#DynamoDB Thread 5"
        }
      }
    }
  },
  {
    "DeleteRequest":{
      "Key":{
        "HashKeyElement":{
          "S":"Amazon DynamoDB#DynamoDB Thread 4"
        },
        "RangeKeyElement":{
          "S":"oops - accidental row"
        }
      }
    }
  }
]
JSON;
        $data = json_decode($json);
        $writeRequestItem = new WriteRequestItem();
        $writeRequestItem->populateFromDynamoDB('Reply', $data);
    }

}