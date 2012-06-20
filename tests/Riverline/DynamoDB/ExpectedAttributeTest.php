<?php

namespace Riverline\DynamoDB;

class ExpectedAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testNewWithValue()
    {
        $expectedAttribute = new ExpectedAttribute('Expected Value');

        $this->assertNull($expectedAttribute->getExists());
        $this->assertEquals('Expected Value', $expectedAttribute->getValue()->getValue());

        return $expectedAttribute;
    }

    /**
     * @depends testNewWithValue
     */
    public function testGetForDynamoDBWithValue(ExpectedAttribute $expectedAttribute)
    {
        $condition = $expectedAttribute->getForDynamoDB();
        $this->assertArrayNotHasKey('Exists', $condition);
        $this->assertEquals(array('S' => 'Expected Value'), $condition['Value']);
    }

    public function testNewWithExists()
    {
        $expectedAttribute = new ExpectedAttribute(false);

        $this->assertEquals(false, $expectedAttribute->getExists());
        $this->assertNull($expectedAttribute->getValue());

        return $expectedAttribute;
    }

    /**
     * @depends testNewWithExists
     */
    public function testGetForDynamoDBWithExists(ExpectedAttribute $expectedAttribute)
    {
        $condition = $expectedAttribute->getForDynamoDB(); 
        $this->assertEquals(false, $condition['Exists']);
        $this->assertArrayNotHasKey('Value', $condition);
    }
}