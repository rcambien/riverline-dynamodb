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
        $this->conn = new Connection(AWS_KEY, AWS_SECRET, '/tmp/', AWS_REGION);
    }

    public function testConnection()
    {
        $this->assertInstanceOf('AmazonDynamoDB', $this->conn->getConnector());
    }

    protected function createRangeItem($range)
    {
        $item = new Item(DY_TABLE_RANGE);
        $item['id']    = ITEM_ID;
        $item['range'] = $range;
        $item['name']  = 'test '.$range;
        return $item;
    }
}
