<?php

namespace Riverline\DynamoDB;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    protected $conn;

    protected function setUp()
    {
        $this->conn = new Connection(getenv('AWS_ACCESS_KEY'), getenv('AWS_SECRET_KEY'), getenv('AWS_REGION'));
    }

    public function testConnection()
    {
        $this->assertInstanceOf('Aws\DynamoDb\DynamoDbClient', $this->conn->getConnector());
    }

    protected function createRangeItem($range)
    {
        $item = new Item(getenv('DY_TABLE_RANGE'));
        $item['id']    = getenv('ITEM_ID');
        $item['range'] = $range;
        $item['name']  = 'test '.$range;
        return $item;
    }
}
