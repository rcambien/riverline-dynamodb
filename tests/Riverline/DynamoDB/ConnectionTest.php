<?php

namespace Riverline\DynamoDB;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConnection()
    {
        $conn = new Connection(AWS_KEY, AWS_SECRET, 'apc', AWS_REGION);

        $this->assertInstanceOf('AmazonDynamoDB', $conn->getConnector());

        return $conn;
    }

    /**
     * @depends testConnection
     */
    public function testCreateItem(Connection $conn)
    {
        $item = new Item(DY_TABLE);
        $item['id']      = 123;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $conn->put($item);
    }

    /**
     * @depends testConnection
     */
    public function testGetUnknowItem(Connection $conn)
    {
        $item = $conn->get(DY_TABLE, 456);

        $this->assertNull($item);
    }

    /**
     * @depends testConnection
     */
    public function testGetItem(Connection $conn)
    {
        $item = $conn->get(DY_TABLE, 123);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(123, $item['id']);
        $this->assertSame('test', $item['name']);
        $this->assertSame(array('test1', 'test2'), $item['strings']);
        $this->assertSame(array(4, 5, 6), $item['numbers']);
    }

    /**
     * @depends testConnection
     */
    public function testGetPartial(Connection $conn)
    {
        $context = new Context\Get();
        $context->setAttributesToGet(array('id'));

        $item = $conn->get(DY_TABLE, 123, null, $context);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(123, $item['id']);
        $this->assertEmpty($item['name']);
    }

    /**
     * @depends testConnection
     */
    public function testGetRangeItem(Connection $conn)
    {
        $conn->put($this->createRangeItem(456));

        $item = $conn->get(DY_TABLE_RANGE, 123, 456);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(123, $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    /**
     * @depends testConnection
     */
    public function testQuery(Connection $conn)
    {
        $items = $conn->query(DY_TABLE_RANGE, 123, Context\Query::create(\AmazonDynamoDB::CONDITION_LESS_THAN, 460));

        $this->assertCount(1, $items);

        $item = $items->shift();

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(123, $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    /**
     * @depends testConnection
     */
    public function testBetweenQuery(Connection $conn)
    {
        $items = $conn->query(DY_TABLE_RANGE, 123, Context\Query::create(\AmazonDynamoDB::CONDITION_BETWEEN, array(400, 500)));

        $this->assertCount(1, $items);

        $item = $items->shift();

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(123, $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    /**
     * @depends testConnection
     */
    public function testQueryLimit(Connection $conn)
    {
        // Add items
        $conn->put($this->createRangeItem(567));
        $conn->put($this->createRangeItem(678));
        $conn->put($this->createRangeItem(789));

        $query = new Context\Query();
        $query->setLimit(2);

        $items = $conn->query(DY_TABLE_RANGE, 123, $query);

        $this->assertInstanceOf('\Riverline\DynamoDB\Collection', $items);

        $this->assertCount(2, $items);

        $item = $items->shift();
        $this->assertSame(456, $item['range']);

        $item = $items->shift();
        $this->assertSame(567, $item['range']);

        $this->assertNotEmpty($items->getLastKey());

        $query->setLastKey($items->getLastKey());
        $query->setLimit(3);

        $items = $conn->query(DY_TABLE_RANGE, 123, $query);

        $this->assertCount(2, $items);

        $item = $items->shift();
        $this->assertSame(678, $item['range']);

        $item = $items->shift();
        $this->assertSame(789, $item['range']);

        $this->assertEmpty($items->getLastKey());
    }

    /**
     * @depends testConnection
     */
    public function testScan(Connection $conn)
    {
        $items = $conn->scan(DY_TABLE_RANGE);

        $this->assertCount(4, $items);
    }

    /**
     * @depends testConnection
     */
    public function testDelete(Connection $conn)
    {
        $conn->delete(DY_TABLE, 123);

        $conn->delete(DY_TABLE_RANGE, 123, 456);

        $conn->delete(DY_TABLE_RANGE, 123, 567);

        $conn->delete(DY_TABLE_RANGE, 123, 678);

        $conn->delete(DY_TABLE_RANGE, 123, 789);
    }

    /**
     * @depends testConnection
     */
    public function testDeleteUnknow(Connection $conn)
    {
        $conn->delete(DY_TABLE, 456);
    }

    protected function createRangeItem($range)
    {
        $item = new Item(DY_TABLE_RANGE);
        $item['id']    = 123;
        $item['range'] = $range;
        $item['name']  = 'test '.$range;
        return $item;
    }
}
