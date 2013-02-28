<?php

namespace Riverline\DynamoDB;

use Aws\DynamoDb\Enum\AttributeAction;

class UpdateActionTest extends \PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $action = new UpdateAction(AttributeAction::ADD, 10);

        $this->assertEquals(AttributeAction::ADD, $action->getAction());
        $this->assertEquals(10, $action->getValue()->getValue());

        return $action;
    }

    /**
     * @depends testNew
     */
    public function testGetForDynamoDB(UpdateAction $action)
    {
        $parameters = $action->getForDynamoDB();
        $this->assertEquals(AttributeAction::ADD, $parameters['Action']);
        $this->assertEquals(array('N' => '10'), $parameters['Value']);
    }
}