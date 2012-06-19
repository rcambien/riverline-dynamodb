<?php

namespace Riverline\DynamoDB;

class ExpectedTest extends \PHPUnit_Framework_TestCase
{
    public function testWriteAttribute()
    {
        $expected = new Expected();

        $expected['name'] = new ExpectedAttribute('expected name');
        $this->assertEquals('expected name', $expected['name']->getValue()->getValue());

        $expected['numbers'] = new ExpectedAttribute(false);
        $this->assertEquals(false, $expected['numbers']->getExists());
    }

}