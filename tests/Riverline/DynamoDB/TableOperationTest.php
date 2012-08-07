<?php

namespace Riverline\DynamoDB;

class TableOperationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    protected $conn;

    protected function setUp()
    {
        $this->conn = new Connection(AWS_KEY, AWS_SECRET, '/tmp/', AWS_REGION);
    }

    public function testDescribeTable()
    {
        $tableDescription = $this->conn->describeTable(DY_TABLE);
        $this->assertInstanceOf('Riverline\DynamoDB\Table\TableDescription', $tableDescription);
    }

    public function testDescribeUnknowTable()
    {
        $this->setExpectedException('\Riverline\DynamoDB\Exception\ServerException');

        $this->conn->describeTable(DY_TABLE_TMP_VER);
    }

    /**
     * @depends testDescribeUnknowTable
     */
    public function testListTables()
    {
        $tables = $tableCollection = $this->conn->listTables(1, DY_TABLE);
        $this->assertCount(1, $tables);
        $this->assertEquals(DY_TABLE_RANGE, $tables->shift());
    }

    /**
     * @depends testDescribeUnknowTable
     */
    public function testTableCreate()
    {
        $hash                  = new Table\KeySchemaElement('id', \AmazonDynamoDB::TYPE_NUMBER);
        $range                 = new Table\KeySchemaElement('range', \AmazonDynamoDB::TYPE_STRING);
        $keySchema             = new \Riverline\DynamoDB\Table\KeySchema($hash, $range);
        $provisionedThroughput = new \Riverline\DynamoDB\Table\ProvisionedThroughput(3, 5);

        $this->conn->createTable(DY_TABLE_TMP_VER, $keySchema, $provisionedThroughput);

        $tableDescription = $this->conn->waitForTableToBeInState(DY_TABLE_TMP_VER, 'ACTIVE');

        $this->assertInstanceOf('Riverline\DynamoDB\Table\TableDescription', $tableDescription);

        $keySchema = $tableDescription->getKeySchema();
        $this->assertInstanceOf('Riverline\DynamoDB\Table\KeySchema', $keySchema);
        $this->assertInstanceOf('Riverline\DynamoDB\Table\KeySchemaElement', $keySchema->getHash());
        $this->assertEquals('id', $keySchema->getHash()->getName());
        $this->assertEquals(\AmazonDynamoDB::TYPE_NUMBER, $keySchema->getHash()->getType());
        $this->assertInstanceOf('Riverline\DynamoDB\Table\KeySchemaElement', $keySchema->getRange());
        $this->assertEquals('range', $keySchema->getRange()->getName());
        $this->assertEquals(\AmazonDynamoDB::TYPE_STRING, $keySchema->getRange()->getType());

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

        $this->conn->updateTable(DY_TABLE_TMP_VER, $provisionedThroughput);

        $tableDescription = $this->conn->waitForTableToBeInState(DY_TABLE_TMP_VER, 'ACTIVE');

        $this->assertInstanceOf('Riverline\DynamoDB\Table\TableDescription', $tableDescription);

        $provisionedThroughput = $tableDescription->getProvisionedThroughput();
        $this->assertInstanceOf('Riverline\DynamoDB\Table\ProvisionedThroughput', $provisionedThroughput);
        $this->assertEquals(5, $provisionedThroughput->getReadCapacity());
        $this->assertEquals(5, $provisionedThroughput->getWriteCapacity());
    }

    public function testTableDelete()
    {
        $this->conn->deleteTable(DY_TABLE_TMP_VER);
    }
}