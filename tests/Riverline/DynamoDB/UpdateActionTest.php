<?php

namespace Riverline\DynamoDB;

class UpdateActionTest extends \PHPUnit_Framework_TestCase
{
    public function testNew()
    {
        $action = new UpdateAction(\AmazonDynamoDB::ACTION_ADD, 10);

        $this->assertEquals(\AmazonDynamoDB::ACTION_ADD, $action->getAction());
        $this->assertEquals(10, $action->getValue()->getValue());

        return $action;
    }

    /**
     * @depends testNew
     */
    public function testGetForDynamoDB(UpdateAction $action)
    {
        $parameters = $action->getForDynamoDB();
        $this->assertEquals(\AmazonDynamoDB::ACTION_ADD, $parameters['Action']);
        $this->assertEquals(array('N' => '10'), $parameters['Value']);
    }
}