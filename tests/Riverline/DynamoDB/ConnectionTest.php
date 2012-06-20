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

    /**
     * @depends testConnection
     */
    public function testUpdate(Connection $conn)
    {
        $item = new Item(DY_TABLE);
        $item['id']      = 123;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $conn->put($item);

        $update = new \Riverline\DynamoDB\AttributeUpdate();
        $update['name'] = new UpdateAction(\AmazonDynamoDB::ACTION_PUT, 'new name');
        $update['strings'] = new UpdateAction(\AmazonDynamoDB::ACTION_ADD, array('test3'));
        $update['numbers'] = new UpdateAction(\AmazonDynamoDB::ACTION_DELETE);

        $attributes = $conn->update(DY_TABLE, 123, null, $update);
        $this->assertNull($attributes);
    }

    /**
     * @depends testConnection
     */
    public function testPutExpected(Connection $conn)
    {
        $item = new Item(DY_TABLE);
        $item['id']      = 123;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $conn->put($item);

        $expected = new Expected();
        $expected['strings'] = new ExpectedAttribute(array('test1', 'test2'));
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Put();
        $context->setExpected($expected);
        $context->setReturnValues(\AmazonDynamoDB::RETURN_ALL_OLD);

        $newItem = new Item(DY_TABLE);
        $newItem['id']      = 123;
        $newItem['name']    = 'test';
        $newItem['strings'] = array('test1', 'test2', 'test3');
        $newItem['numbers'] = array(4, 5, 6, 7, 8, 9);

        $attributes = $conn->put($item, $context);
        $this->assertNotNull($attributes);
    }

    /**
     * @depends testConnection
     */
    public function testDeleteExpected(Connection $conn)
    {
        $item = new Item(DY_TABLE);
        $item['id']      = 123;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $conn->put($item);

        $expected = new Expected();
        $expected['name'] = new ExpectedAttribute('test');
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Delete();
        $context->setExpected($expected);
        $context->setReturnValues(\AmazonDynamoDB::RETURN_ALL_OLD);

        $attributes = $conn->delete(DY_TABLE, 123, null, $context);
        $this->assertNotNull($attributes);
    }

    /**
     * @depends testConnection
     */
    public function testUpdateExpected(Connection $conn)
    {
        $item = new Item(DY_TABLE);
        $item['id']      = 123;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $conn->put($item);

        $expected = new Expected();
        $expected['name'] = new ExpectedAttribute('test');
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Update();
        $context->setExpected($expected);
        $context->setReturnValues(\AmazonDynamoDB::RETURN_UPDATED_NEW);

        $update = new \Riverline\DynamoDB\AttributeUpdate();
        $update['name'] = new UpdateAction(\AmazonDynamoDB::ACTION_PUT, 'new name');
        $update['strings'] = new UpdateAction(\AmazonDynamoDB::ACTION_ADD, array('test3'));
        $update['numbers'] = new UpdateAction(\AmazonDynamoDB::ACTION_DELETE);

        $attributes = $conn->update(DY_TABLE, 123, null, $update, $context);
        $this->assertNotNull($attributes);
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
