<?php

namespace Riverline\DynamoDB;

use Aws\DynamoDb\Enum\Type;

class TableOperationTest extends ConnectionTest
{
    public function testDescribeTable()
    {
        $tableDescription = $this->conn->describeTable(getenv('DY_TABLE'));
        $this->assertInstanceOf('Riverline\DynamoDB\Table\TableDescription', $tableDescription);
    }

    public function testDescribeUnknowTable()
    {
        $this->setExpectedException('Aws\DynamoDb\Exception\ResourceNotFoundException');

        $this->conn->describeTable(getenv('DY_TABLE_TMP_VER'));
    }

    /**
     * @depends testDescribeUnknowTable
     */
    public function testListTables()
    {
        $tables = $tableCollection = $this->conn->listTables(1, getenv('DY_TABLE'));
        $this->assertCount(1, $tables);
        $this->assertEquals(getenv('DY_TABLE_RANGE'), $tables->shift());
    }

    /**
     * @depends testDescribeUnknowTable
     */
    public function testTableCreate()
    {
        $hash                  = new Table\KeySchemaElement('id', Type::NUMBER);
        $range                 = new Table\KeySchemaElement('range', Type::STRING);
        $keySchema             = new \Riverline\DynamoDB\Table\KeySchema($hash, $range);
        $provisionedThroughput = new \Riverline\DynamoDB\Table\ProvisionedThroughput(3, 5);

        $this->conn->createTable(getenv('DY_TABLE_TMP_VER'), $keySchema, $provisionedThroughput);

        $tableDescription = $this->conn->waitForTableToBeInState(getenv('DY_TABLE_TMP_VER'), 'ACTIVE');

        $this->assertInstanceOf('Riverline\DynamoDB\Table\TableDescription', $tableDescription);

        $keySchema = $tableDescription->getKeySchema();
        $this->assertInstanceOf('Riverline\DynamoDB\Table\KeySchema', $keySchema);
        $this->assertInstanceOf('Riverline\DynamoDB\Table\KeySchemaElement', $keySchema->getHash());
        $this->assertEquals('id', $keySchema->getHash()->getName());
        $this->assertEquals(Type::NUMBER, $keySchema->getHash()->getType());
        $this->assertInstanceOf('Riverline\DynamoDB\Table\KeySchemaElement', $keySchema->getRange());
        $this->assertEquals('range', $keySchema->getRange()->getName());
        $this->assertEquals(Type::STRING, $keySchema->getRange()->getType());

        $provisionedThroughput = $tableDescription->getProvisionedThroughput();
        $this->assertInstanceOf('Riverline\DynamoDB\Table\ProvisionedThroughput', $provisionedThroughput);
        $this->assertEquals(3, $provisionedThroughput->getReadCapacity());
        $this->assertEquals(5, $provisionedThroughput->getWriteCapacity());
    }

    /**
     * @depends testTableCreate
     */
    public function testTableUpdate()
    {
        $provisionedThroughput = new \Riverline\DynamoDB\Table\ProvisionedThroughput(5, 5);

        $this->conn->updateTable(getenv('DY_TABLE_TMP_VER'), $provisionedThroughput);

        $tableDescription = $this->conn->waitForTableToBeInState(getenv('DY_TABLE_TMP_VER'), 'ACTIVE');

        $this->assertInstanceOf('Riverline\DynamoDB\Table\TableDescription', $tableDescription);

        $provisionedThroughput = $tableDescription->getProvisionedThroughput();
        $this->assertInstanceOf('Riverline\DynamoDB\Table\ProvisionedThroughput', $provisionedThroughput);
        $this->assertEquals(5, $provisionedThroughput->getReadCapacity());
        $this->assertEquals(5, $provisionedThroughput->getWriteCapacity());
    }

    public function testTableDelete()
    {
        $this->conn->deleteTable(getenv('DY_TABLE_TMP_VER'));
    }
}