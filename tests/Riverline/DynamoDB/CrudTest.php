<?php

namespace Riverline\DynamoDB;

use Aws\DynamoDb\Enum\ComparisonOperator;
use Aws\DynamoDb\Enum\AttributeAction;
use Aws\DynamoDb\Enum\ReturnValue;

class CrudTest extends ConnectionTest
{
    public function testCreateItem()
    {
        $item = new Item(getenv('DY_TABLE'));
        $item['id']      = getenv('ITEM_ID');
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);
    }

    public function testServerError()
    {
        $this->setExpectedException('\Aws\DynamoDb\Exception\ValidationException');

        $this->conn->get(getenv('DY_TABLE_RANGE'), getenv('ITEM_ID'));
    }

    public function testGetUnknowItem()
    {
        $item = $this->conn->get(getenv('DY_TABLE'), getenv('ITEM_ID')+1);

        $this->assertNull($item);
    }

    public function testGetItem()
    {
        $id = intval(getenv('ITEM_ID'));

        $item = $this->conn->get(getenv('DY_TABLE'), $id);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame($id, $item['id']);
        $this->assertSame('test', $item['name']);
        $this->assertSame(array('test1', 'test2'), $item['strings']);
        $this->assertSame(array(4, 5, 6), $item['numbers']);
    }

    public function testArrayCopy()
    {
        $id = intval(getenv('ITEM_ID'));

        $item = $this->conn->get(getenv('DY_TABLE'), $id);

        $this->assertSame(array (
            'id' => $id,
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

        $id = intval(getenv('ITEM_ID'));

        $item = $this->conn->get(getenv('DY_TABLE'), $id, null, $context);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame($id, $item['id']);
        $this->assertEmpty($item['name']);
    }

    public function testGetRangeItem()
    {
        $this->conn->put($this->createRangeItem(456));

        $id = intval(getenv('ITEM_ID'));

        $item = $this->conn->get(getenv('DY_TABLE_RANGE'), $id, 456);

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame($id, $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    public function testQuery()
    {
        $items = $this->conn->query(getenv('DY_TABLE_RANGE'), getenv('ITEM_ID'), Context\Query::create(ComparisonOperator::LT, 460));

        $this->assertCount(1, $items);

        $item = $items->shift();

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame(intval(getenv('ITEM_ID')), $item['id']);
        $this->assertSame(456, $item['range']);
        $this->assertSame('test 456', $item['name']);
    }

    public function testBetweenQuery()
    {
        $id = intval(getenv('ITEM_ID'));

        $items = $this->conn->query(getenv('DY_TABLE_RANGE'), $id, Context\Query::create(ComparisonOperator::BETWEEN, array(400, 500)));

        $this->assertCount(1, $items);

        $item = $items->shift();

        $this->assertInstanceOf('\Riverline\DynamoDB\Item', $item);

        $this->assertSame($id, $item['id']);
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

        $id = intval(getenv('ITEM_ID'));

        $items = $this->conn->query(getenv('DY_TABLE_RANGE'), $id, $query);

        $this->assertInstanceOf('\Riverline\DynamoDB\Collection', $items);

        $this->assertCount(2, $items);

        $item = $items->shift();
        $this->assertSame(456, $item['range']);

        $item = $items->shift();
        $this->assertSame(567, $item['range']);

        $this->assertNotEmpty($items->getNextContext());

        $query = $items->getNextContext();
        $query->setLimit(3);

        $items = $this->conn->query(getenv('DY_TABLE_RANGE'), $id, $query);

        $this->assertCount(2, $items);

        $item = $items->shift();
        $this->assertSame(678, $item['range']);

        $item = $items->shift();
        $this->assertSame(789, $item['range']);

        $this->assertEmpty($items->getNextContext());
    }

    public function testScan()
    {
        $scan = new Context\Scan();
        $scan->addFilter('id', ComparisonOperator::EQ, getenv('ITEM_ID'));

        $items = $this->conn->scan(getenv('DY_TABLE_RANGE'), $scan);

        $this->assertCount(4, $items);
    }

    public function testScanWithArray()
    {
        $id = intval(getenv('ITEM_ID'));

        $item = $this->conn->get(getenv('DY_TABLE_RANGE'), $id, 456);

        $item['strings'] = array('one', 'two');

        $this->conn->put($item);

        $scan = new Context\Scan();
        $scan->addFilter('id', ComparisonOperator::EQ, getenv('ITEM_ID'));
        $scan->addFilter('strings', ComparisonOperator::CONTAINS, 'one');

        $items = $this->conn->scan(getenv('DY_TABLE_RANGE'), $scan);

        foreach ($items as $item) {
            /** @var $item Item */
            $this->assertSame(array (
                'id'    => $id,
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
        $this->conn->delete(getenv('DY_TABLE'), getenv('ITEM_ID'));

        $this->conn->delete(getenv('DY_TABLE_RANGE'), getenv('ITEM_ID'), 456);

        $this->conn->delete(getenv('DY_TABLE_RANGE'), getenv('ITEM_ID'), 567);

        $this->conn->delete(getenv('DY_TABLE_RANGE'), getenv('ITEM_ID'), 678);

        $this->conn->delete(getenv('DY_TABLE_RANGE'), getenv('ITEM_ID'), 789);
    }

    public function testDeleteUnknow()
    {
        $this->conn->delete(getenv('DY_TABLE'), 456);
    }

    public function testUpdate()
    {
        $id = intval(getenv('ITEM_ID'));

        $item = new Item(getenv('DY_TABLE'));
        $item['id']      = $id;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

        $update = new \Riverline\DynamoDB\AttributeUpdate();
        $update['name'] = new UpdateAction(AttributeAction::PUT, 'new name');
        $update['strings'] = new UpdateAction(AttributeAction::ADD, array('test3'));
        $update['numbers'] = new UpdateAction(AttributeAction::DELETE);

        $attributes = $this->conn->update(getenv('DY_TABLE'), $id, null, $update);
        $this->assertNull($attributes);
    }

    public function testPutExpected()
    {
        $id = intval(getenv('ITEM_ID'));

        $item = new Item(getenv('DY_TABLE'));
        $item['id']      = $id;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

        $expected = new Expected();
        $expected['strings'] = new ExpectedAttribute(array('test1', 'test2'));
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Put();
        $context->setExpected($expected);
        $context->setReturnValues(ReturnValue::ALL_OLD);

        $newItem = new Item(getenv('DY_TABLE'));
        $newItem['id']      = $id;
        $newItem['name']    = 'test';
        $newItem['strings'] = array('test1', 'test2', 'test3');
        $newItem['numbers'] = array(4, 5, 6, 7, 8, 9);

        $attributes = $this->conn->put($item, $context);
        $this->assertNotNull($attributes);
    }

    public function testDeleteExpected()
    {
        $id = intval(getenv('ITEM_ID'));

        $item = new Item(getenv('DY_TABLE'));
        $item['id']      = $id;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

        $expected = new Expected();
        $expected['name'] = new ExpectedAttribute('test');
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Delete();
        $context->setExpected($expected);
        $context->setReturnValues(ReturnValue::ALL_OLD);

        $attributes = $this->conn->delete(getenv('DY_TABLE'), $id, null, $context);
        $this->assertNotNull($attributes);
    }

    public function testUpdateExpected()
    {
        $id = intval(getenv('ITEM_ID'));

        $item = new Item(getenv('DY_TABLE'));
        $item['id']      = $id;
        $item['name']    = 'test';
        $item['strings'] = array('test1', 'test2');
        $item['numbers'] = array(4, 5, 6);

        $this->conn->put($item);

        $expected = new Expected();
        $expected['name'] = new ExpectedAttribute('test');
        $expected['non_existent'] = new ExpectedAttribute(false);

        $context = new Context\Update();
        $context->setExpected($expected);
        $context->setReturnValues(ReturnValue::UPDATED_NEW);

        $update = new \Riverline\DynamoDB\AttributeUpdate();
        $update['name'] = new UpdateAction(AttributeAction::PUT, 'new name');
        $update['strings'] = new UpdateAction(AttributeAction::ADD, array('test3'));
        $update['numbers'] = new UpdateAction(AttributeAction::DELETE);

        $attributes = $this->conn->update(getenv('DY_TABLE'), $id, null, $update, $context);
        $this->assertNotNull($attributes);
    }
}
