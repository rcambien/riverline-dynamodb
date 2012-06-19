<?php

namespace Riverline\DynamoDB;

class AttributeUpdateTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteAttribute()
    {
        $attributeUpdate = new AttributeUpdate();

        $attributeUpdate['name'] = new UpdateAction(\AmazonDynamoDB::ACTION_PUT, 'new name');
        $this->assertEquals(\AmazonDynamoDB::ACTION_PUT, $attributeUpdate['name']->getAction());
        $this->assertEquals('new name', $attributeUpdate['name']->getValue()->getValue());

        $attributeUpdate['numbers'] = new UpdateAction(\AmazonDynamoDB::ACTION_ADD, array(10, 11));
        $this->assertEquals(\AmazonDynamoDB::ACTION_ADD, $attributeUpdate['numbers']->getAction());
        $this->assertEquals(array(10, 11), $attributeUpdate['numbers']->getValue()->getValue());
    }

}