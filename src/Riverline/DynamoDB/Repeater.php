<?php

namespace Riverline\DynamoDB;

/**
 * @class
 */
class Repeater
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     * @param Context\Scan $context
     * @return \Riverline\DynamoDB\Collection
     */
    public function scan($table, Context\Scan $context = null)
    {
        $collection = new Collection();
        do {
            try {
                $items = $this->connection->scan($table, $context);
                $collection->merge($items);
                if ($items->more()) {
                    $context = $items->getNextContext();
                } else {
                    // End
                    break;
                }
            } catch (\Riverline\DynamoDB\Exception\ProvisionedThroughputExceededException $e) {
                // Continue
            }
        } while(true);

        return $collection;
    }

    /**
     * @param string $table
     * @param mixed $hash
     * @param Context\Query $context
     * @return \Riverline\DynamoDB\Collection
     */
    public function query($table, $hash, Context\Query $context = null)
    {
        $collection = new Collection();
        do {
            try {
                $items = $this->connection->query($table, $context);
                $collection->merge($items);
                if ($items->more()) {
                    $context = $items->getNextContext();
                } else {
                    // End
                    break;
                }
            } catch (\Riverline\DynamoDB\Exception\ProvisionedThroughputExceededException $e) {
                // Continue
            }
        } while(true);

        return $collection;
    }

    /**
     * @param Context\BatchGet $context
     * @return Collection
     */
    public function batchGet(Context\BatchGet $context)
    {
        $batchCollection = new \Riverline\DynamoDB\Batch\BatchCollection();
        do {
            try {
                $itemsByTable = $this->connection->batchGet($context);
                foreach ($itemsByTable as $table => $items) {
                    /** @var $collection Collection */
                    $collection = $batchCollection[$table];

                    if (null === $collection) {
                        $batchCollection[$table] = $items;
                    } else {
                        $collection->merge($items);
                        $batchCollection[$table] = $collection;
                    }
                }
                if ($itemsByTable->more()) {
                    $context = $itemsByTable->getUnprocessedKeysContext();
                } else {
                    // End
                    break;
                }
            } catch (\Riverline\DynamoDB\Exception\ProvisionedThroughputExceededException $e) {
                // Continue
            }
        } while(true);

        return $batchCollection;
    }

    public function batchWrite(Context\BatchWrite $context)
    {
        do {
            try {
                $context = $this->connection->batchWrite($context);
            } catch (\Riverline\DynamoDB\Exception\ProvisionedThroughputExceededException $e) {
                // Continue
            }
        } while(null !== $context);
    }
}
