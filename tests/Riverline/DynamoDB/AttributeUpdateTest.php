<?php

namespace Riverline\DynamoDB;

use Aws\DynamoDb\Enum\AttributeAction;

class AttributeUpdateTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteAttribute()
    {
        $attributeUpdate = new AttributeUpdate();

        $attributeUpdate['name'] = new UpdateAction(AttributeAction::PUT, 'new name');
        $this->assertEquals(AttributeAction::PUT, $attributeUpdate['name']->getAction());
        $this->assertEquals('new name', $attributeUpdate['name']->getValue()->getValue());

        $attributeUpdate['numbers'] = new UpdateAction(AttributeAction::ADD, array(10, 11));
        $this->assertEquals(AttributeAction::ADD, $attributeUpdate['numbers']->getAction());
        $this->assertEquals(array(10, 11), $attributeUpdate['numbers']->getValue()->getValue());
    }

}