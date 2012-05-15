README
======

What is Riverline\DynamoDB
--------------------------

``Riverline\DynamoDB`` is a PHP 5.3 object wrapper for the Amazon PHP DynamoDB SDK.
It speed up the manipulation of items and attributes

Requirements
------------

* PHP 5.3.5
* AWS PHP SDK 1.5.3

Installation
------------

``Riverline\DynamoDB`` is compatible composer and any prs-0 autoloader

Getting started
---------------

    // Create a DynamoDB connection
    $connection = new \Riverline\DynamoDB\Connection('AccessKey', 'SecretKey', 'apc');

    // Create an item
    // Product is a table with hash key 'id' and range key 'subid'
    $product = new \Riverline\DynamoDB\Item('Product');
    $product['id']    = 102;
    $product['subid'] = 202;
    $product['title'] = "Product 102-202";
    $product['authors'] = array('Author1', 'Author2');

    // Save it
    $connection->put($product);

    // Get It
    $product = $connection->get('Product', 102, 202);

    // Query It with consistent read
    $context = new \Riverline\DynamoDB\Context\Query();
    $context->setRangeCondition(\AmazonDynamoDB::CONDITION_BETWEEN, array(200, 205));
    $context->setConsistentRead(true);
    $items = $connection->query('Product', 102, $context);

    // Scan It with limit
    $context = new \Riverline\DynamoDB\Context\Scan();
    $context->addFilter('title', \AmazonDynamoDB::CONDITION_CONTAINS, 'Product');
    $context->addFilter('authors', \AmazonDynamoDB::CONDITION_CONTAINS, 'Author1');
    $context->setLimit(1);
    $items = $connection->scan('Product', $context);

    if ($items->more()) {
        // more results to get
        $context->setLastKey($items->getLastKey());
        $moreItems = $connection->scan('Product', $context);
    }

    // Delete it
    $connection->delete('Product', 102, 202);

    // Get consumed read unit
    echo $connection->getConsumedReadUnits();




