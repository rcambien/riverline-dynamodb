<?php

namespace Riverline\DynamoDB;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $item = new Item(getenv('DY_TABLE'));

        $this->assertEquals(getenv('DY_TABLE'), $item->getTable());

        return $item;
    }

    /**
     * @depends testNew
     */
    public function testWriteAttribute(Item $item)
    {
        $item['id'] = 123;
        $this->assertEquals(123, $item['id']);

        $item['name'] = 'test';
        $this->assertEquals('test', $item['name']);
    }
}