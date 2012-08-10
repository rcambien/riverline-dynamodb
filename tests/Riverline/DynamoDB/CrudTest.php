<?php

namespace Riverline\DynamoDB;

require_once 'ConnectionTest.php';

class CrudTest extends ConnectionTest
{
    public function testCreateItem()
    {
        $item = new Item(DY_TABLE);
        $item['id']      = ITEM_ID;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);
    }

    public function testServerError()
    {
        $this->setExpectedException('\Riverline\DynamoDB\Exception\ServerException');

        $this->conn->get(DY_TABLE_RANGE, ITEM_ID);
    }

    public function testGetUnknowItem()
    {
        $item = $this->conn->get(DY_TABLE, ITEM_ID+1);

        $this->assertNull($item);
    }

    public function testGetItem()
    {
        $item = $this->conn->get(DY_TABLE, ITEM_ID);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(ITEM_ID, $item['id']);
        $this->assertSame('test', $item['name']);
        $this->assertSame(array('test1', 'test2'), $item['strings']);
        $this->assertSame(array(4, 5, 6), $item['numbers']);
    }

    public function testArrayCopy()
    {
        $item = $this->conn->get(DY_TABLE, ITEM_ID);

        $this->assertSame(array (
            'id' => ITEM_ID,
            'name' => 'test',
            'numbers' => array (
                0 => 4,
                1 => 5,
                2 => 6,
            ),
            'strings' => array (
                0 => 'test1',
                1 => 'test2',
            )
        ), $item->getArrayCopy());
    }

    public function testGetPartial()
    {
        $context = new Context\Get();
        $context->setAttributesToGet(array('id'));

        $item = $this->conn->get(DY_TABLE, ITEM_ID, null, $context);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(ITEM_ID, $item['id']);
        $this->assertEmpty($item['name']);
    }

    public function testGetRangeItem()
    {
        $this->conn->put($this->createRangeItem(456));

        $item = $this->conn->get(DY_TABLE_RANGE, ITEM_ID, 456);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(ITEM_ID, $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    public function testQuery()
    {
        $items = $this->conn->query(DY_TABLE_RANGE, ITEM_ID, Context\Query::create(\AmazonDynamoDB::CONDITION_LESS_THAN, 460));

        $this->assertCount(1, $items);

        $item = $items->shift();

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(ITEM_ID, $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    public function testBetweenQuery()
    {
        $items = $this->conn->query(DY_TABLE_RANGE, ITEM_ID, Context\Query::create(\AmazonDynamoDB::CONDITION_BETWEEN, array(400, 500)));

        $this->assertCount(1, $items);

        $item = $items->shift();

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(ITEM_ID, $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    public function testQueryLimit()
    {
        // Add items
        $this->conn->put($this->createRangeItem(567));
        $this->conn->put($this->createRangeItem(678));
        $this->conn->put($this->createRangeItem(789));

        $query = new Context\Query();
        $query->setLimit(2);

        $items = $this->conn->query(DY_TABLE_RANGE, ITEM_ID, $query);

        $this->assertInstanceOf('\Riverline\DynamoDB\Collection', $items);

        $this->assertCount(2, $items);

        $item = $items->shift();
        $this->assertSame(456, $item['range']);

        $item = $items->shift();
        $this->assertSame(567, $item['range']);

        $this->assertNotEmpty($items->getLastKey());

        $query->setLastKey($items->getLastKey());
        $query->setLimit(3);

        $items = $this->conn->query(DY_TABLE_RANGE, ITEM_ID, $query);

        $this->assertCount(2, $items);

        $item = $items->shift();
        $this->assertSame(678, $item['range']);

        $item = $items->shift();
        $this->assertSame(789, $item['range']);

        $this->assertEmpty($items->getLastKey());
    }

    public function testScan()
    {
        $scan = new Context\Scan();
        $scan->addFilter('id', \AmazonDynamoDB::CONDITION_EQUAL, ITEM_ID);

        $items = $this->conn->scan(DY_TABLE_RANGE, $scan);

        $this->assertCount(4, $items);
    }

    public function testScanWithArray()
    {
        $item = $this->conn->get(DY_TABLE_RANGE, ITEM_ID, 456);

        $item['strings'] = array('one', 'two');

        $this->conn->put($item);

        $scan = new Context\Scan();
        $scan->addFilter('id', \AmazonDynamoDB::CONDITION_EQUAL, ITEM_ID);
        $scan->addFilter('strings', \AmazonDynamoDB::CONDITION_CONTAINS, 'one');

        $items = $this->conn->scan(DY_TABLE_RANGE, $scan);

        foreach ($items as $item) {
            $this->assertSame(array (
                'id'    => ITEM_ID,
                'name'  => 'test 456',
                'range' => 456,
                'strings' => array (
                    0 => 'one',
                    1 => 'two',
                )
            ), $item->getArrayCopy());

            break;
        }
    }

    public function testDelete()
    {
        $this->conn->delete(DY_TABLE, ITEM_ID);

        $this->conn->delete(DY_TABLE_RANGE, ITEM_ID, 456);

        $this->conn->delete(DY_TABLE_RANGE, ITEM_ID, 567);

        $this->conn->delete(DY_TABLE_RANGE, ITEM_ID, 678);

        $this->conn->delete(DY_TABLE_RANGE, ITEM_ID, 789);
    }

    public function testDeleteUnknow()
    {
        $this->conn->delete(DY_TABLE, 456);
    }

    public function testUpdate()
    {
        $item = new Item(DY_TABLE);
        $item['id']      = ITEM_ID;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

        $update = new \Riverline\DynamoDB\AttributeUpdate();
        $update['name'] = new UpdateAction(\AmazonDynamoDB::ACTION_PUT, 'new name');
        $update['strings'] = new UpdateAction(\AmazonDynamoDB::ACTION_ADD, array('test3'));
        $update['numbers'] = new UpdateAction(\AmazonDynamoDB::ACTION_DELETE);

        $attributes = $this->conn->update(DY_TABLE, ITEM_ID, null, $update);
        $this->assertNull($attributes);
    }

    public function testPutExpected()
    {
        $item = new Item(DY_TABLE);
        $item['id']      = ITEM_ID;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

        $expected = new Expected();
        $expected['strings'] = new ExpectedAttribute(array('test1', 'test2'));
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Put();
        $context->setExpected($expected);
        $context->setReturnValues(\AmazonDynamoDB::RETURN_ALL_OLD);

        $newItem = new Item(DY_TABLE);
        $newItem['id']      = ITEM_ID;
        $newItem['name']    = 'test';
        $newItem['strings'] = array('test1', 'test2', 'test3');
        $newItem['numbers'] = array(4, 5, 6, 7, 8, 9);

        $attributes = $this->conn->put($item, $context);
        $this->assertNotNull($attributes);
    }

    public function testDeleteExpected()
    {
        $item = new Item(DY_TABLE);
        $item['id']      = ITEM_ID;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

        $expected = new Expected();
        $expected['name'] = new ExpectedAttribute('test');
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Delete();
        $context->setExpected($expected);
        $context->setReturnValues(\AmazonDynamoDB::RETURN_ALL_OLD);

        $attributes = $this->conn->delete(DY_TABLE, ITEM_ID, null, $context);
        $this->assertNotNull($attributes);
    }

    public function testUpdateExpected()
    {
        $item = new Item(DY_TABLE);
        $item['id']      = ITEM_ID;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

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

        $attributes = $this->conn->update(DY_TABLE, ITEM_ID, null, $update, $context);
        $this->assertNotNull($attributes);
    }
}
