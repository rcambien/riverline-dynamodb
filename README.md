# README

[![Build Status](https://secure.travis-ci.org/rcambien/riverline-dynamodb.png)](http://travis-ci.org/rcambien/riverline-dynamodb)

## What is Riverline\DynamoDB

``Riverline\DynamoDB`` is a PHP 5.3 object wrapper for the Amazon PHP DynamoDB SDK.
It speed up the manipulation of items and attributes

## Requirements

* PHP 5.3.5
* AWS PHP SDK 2.1.2

## Installation

``Riverline\DynamoDB`` is compatible with composer and any prs-0 autoloader

## Migration from 1.* to 2.*

Since version 2.0.0, the lib use the new AWS PHP SDK 2.*
To migrate to the new version, you need to fix any call to the old AmazonDynamoDB class by using the new Enum classes.
For example, \AmazonDynamoDB::TYPE_NUMBER become \Aws\DynamoDb\Enum\Type::NUMBER.

The Connection constructor also change.

## Test

Create DynamoDB tables :
* Test(id)
* TestRange(id, [range])

Define phpunit env vars in ``phpunit.xml.dist`

    $ php composer.phar install
    $ phpunit

## Getting started

### Create a new connection

```php
<?php

// Create a DynamoDB connection
$connectionEU = new \Riverline\DynamoDB\Connection('AccessKey', 'SecretKey', \Aws\Common\Enum\Region::EU_WEST_1);

// Attach a simple logger (or write your own logger class)
$connection->setLogger(new \Riverline\DynamoDB\Logger\EchoLogger());
?>
```

### Table operations

```php
<?php

// List Table
$tables = $connection->listTables();

foreach ($tables as $table) {
    echo $table;
}

// Create a new table
$hash                  = new \Riverline\DynamoDB\Table\KeySchemaElement('id', \Aws\DynamoDb\Enum\Type::NUMBER);
$range                 = new \Riverline\DynamoDB\Table\KeySchemaElement('subid', \Aws\DynamoDb\Enum\Type::NUMBER);
$keySchema             = new \Riverline\DynamoDB\Table\KeySchema($hash, $range);
$provisionedThroughput = new \Riverline\DynamoDB\Table\ProvisionedThroughput(3 /* Read */, 5 /* Write */);

$connection->createTable('Product', $keySchema, $provisionedThroughput);

// Wait until active
$connection->waitForTableToBeInState('Product', 'ACTIVE');

// Describe it
$tableDescription = $connection->describeTable('Product');

echo 'Read capacity : '.$tableDescription->getProvisionedThroughput()->getReadCapacity().PHP_EOL;
echo 'Hash key : '.$tableDescription->getKeySchema()->getHash()->getName().PHP_EOL;

// Update it
$provisionedThroughput = new \Riverline\DynamoDB\Table\ProvisionedThroughput(5 /* Read */, 10 /* Write */);
$connection->updateTable('Product', $provisionedThroughput);

// Delete it
$connection->deleteTable('Product');

?>
```

### CRUD

```php
<?php

// Create an item
// Product is a table with hash key 'id' and range key 'subid'
$product = new \Riverline\DynamoDB\Item('Product');
$product['id']    = 102;
$product['subid'] = 202;
$product['title'] = "Product 102-202";
$product['authors'] = array('Author1', 'Author2');

// Save it
$connection->put($product);

// Get it
$product = $connection->get('Product', 102, 202);
echo $product['title'].PHP_EOL;

// Query it on with range condition
$context = new \Riverline\DynamoDB\Context\Query();
$context->setRangeCondition(\Aws\DynamoDb\Enum\ComparisonOperator::BETWEEN, array(200, 205));
$products = $connection->query('Product', 102, $context);

foreach($products as $product) {
    echo $product['title'].PHP_EOL;
}

// Scan it by title and authors fields
$context = new \Riverline\DynamoDB\Context\Scan();
$context->addFilter('title', \Aws\DynamoDb\Enum\ComparisonOperator::CONTAINS, 'Product');
$context->addFilter('authors', \Aws\DynamoDb\Enum\ComparisonOperator::CONTAINS, 'Author1');
$products = $connection->scan('Product', $context);

foreach($products as $product) {
    echo $product['title'].PHP_EOL;
}

// Delete it
$connection->delete('Product', 102, 202);

?>
```

### Complex Query

```php
<?php

// Query with range condition, limit and consistent read
$context = new \Riverline\DynamoDB\Context\Query();
$context->setRangeCondition(\Aws\DynamoDb\Enum\ComparisonOperator::BETWEEN, array(200, 205));
$context->setLimit(2);
$context->setConsistentRead(true);
$products = $connection->query('Product', 102, $context);

// Return only some attributes with backward index
$context = new \Riverline\DynamoDB\Context\Query();
$context->setRangeCondition(\Aws\DynamoDb\Enum\ComparisonOperator::BETWEEN, array(200, 205));
$context->setAttributesToGet(array('id', 'title'));
$context->ScanIndexForward(false);
$products = $connection->query('Product', 102, $context);

?>
```

### Complex Scan

```php
<?php

// Scan with limit
$context = new \Riverline\DynamoDB\Context\Scan();
$context->addFilter('title', \Aws\DynamoDb\Enum\ComparisonOperator::CONTAINS, 'Product');

do {
    $products = $connection->scan('Product', $context);

    foreach($products as $product) {
        echo $product['title'].PHP_EOL;
    }

    // Get the next context to continue
    $context = $products->getNextContext();
} while ($products->more() /* more products to retrieve */);

?>
```

### Batch operations

```php
<?php

// Batch Write
$batchWrite = new \Riverline\DynamoDB\Context\BatchWrite();

// Add some products
for($i=1; $i < 20; $i++) {
    $product = new \Riverline\DynamoDB\Item('Product');
    $product['id']    = 102;
    $product['subid'] = $i;
    $product['title'] = "Product 102-".$i;
    $product['authors'] = array('Author1', 'Author2');

    $batchWrite->addItemToPut($product);
}

// Delete one product
$batchWrite->addKeyToDelete('Product', 102, 202);

$connection->batchWrite($batchWrite);

// Batch Get
$batchGet = new \Riverline\DynamoDB\Context\BatchGet();
$batchGet->addKey('Product', 102, 1);
$batchGet->addKey('Product', 102, 3);
$batchGet->addKey('Product', 102, 5);
$batchGet->addKey('Product', 102, 15);

do {
    $result = $connection->batchGet($batchGet);

    foreach ($result['Product'] as $product) {
        echo $product['title'].PHP_EOL;
    }

    // Get the new batchGet context with the unprocessed keys
    $batchGet = $result->getUnprocessedKeysContext();
} while($batchGet /* $batch is null if there are not unprocessed keys */);

// Repeater helper class

$repeater = new \Riverline\DynamoDB\Repeater($connection);
// Repeat batchWrite request until everything is processed
// Manage "provisioned throughput exceeded" server errors
$repeater->batchWrite($batchWrite);

?>
```

### Get statistics


```php
<?php

// Get consumed write units
echo $connection->getConsumedWriteUnits('Product').PHP_EOL;

// Get consumed read units for all tables
echo $connection->getConsumedReadUnits().PHP_EOL;

?>
```