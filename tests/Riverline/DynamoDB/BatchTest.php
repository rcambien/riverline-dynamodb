<?php

namespace Riverline\DynamoDB;

require_once 'ConnectionTest.php';

class BatchTest extends ConnectionTest
{
    public function testBatchWrite()
    {
        $batch = new Context\BatchWrite();

        $item = $this->createRangeItem(999);
        $batch->addItemToPut($item);

        $item = $this->createRangeItem(998);
        $batch->addItemToPut($item);

        $repeater = new Repeater($this->conn);
        $repeater->BatchWrite($batch);
    }

    public function testBatchWriteBigData()
    {
        $batch = new Context\BatchWrite();

        $bigData = base64_encode(print_r($_SERVER, true));

        $item = $this->createRangeItem(997);
        $item['data'] = $bigData;
        $batch->addItemToPut($item);

        $item = $this->createRangeItem(996);
        $item['data'] = $bigData;
        $batch->addItemToPut($item);

        $repeater = new Repeater($this->conn);
        $repeater->BatchWrite($batch);
    }


    public function testBatchGet()
    {
        $batch = new Context\BatchGet();
        $batch
            ->addKey(DY_TABLE_RANGE, ITEM_ID, 999)
            ->addKey(DY_TABLE_RANGE, ITEM_ID, 998)
            ->addKey(DY_TABLE_RANGE, ITEM_ID, 997)
            ->setAttributesToGet(DY_TABLE_RANGE, array('id'));

        $result = $this->conn->batchGet($batch);

        $this->assertCount(3, $result);
        $this->assertCount(3, $result[DY_TABLE_RANGE]);

        $item = $result[DY_TABLE_RANGE]->shift();
        $this->assertEquals(ITEM_ID, $item['id']);
        $this->assertNull($item['name']);
    }

    public function testBatchWriteAndDelete()
    {
        $batch = new Context\BatchWrite();

        $item = $this->createRangeItem(995);
        $batch->addItemToPut($item);

        $item = $this->createRangeItem(994);
        $batch->addItemToPut($item);

        $batch->addKeyToDelete(DY_TABLE_RANGE, ITEM_ID, 999);
        $batch->addKeyToDelete(DY_TABLE_RANGE, ITEM_ID, 998);

        $repeater = new Repeater($this->conn);
        $repeater->BatchWrite($batch);
    }


    public function testBatchdDelete()
    {
        $batch = new Context\BatchWrite();

        $batch->addKeyToDelete(DY_TABLE_RANGE, ITEM_ID, 997);
        $batch->addKeyToDelete(DY_TABLE_RANGE, ITEM_ID, 996);
        $batch->addKeyToDelete(DY_TABLE_RANGE, ITEM_ID, 995);
        $batch->addKeyToDelete(DY_TABLE_RANGE, ITEM_ID, 994);

        $repeater = new Repeater($this->conn);
        $repeater->BatchWrite($batch);
    }
}