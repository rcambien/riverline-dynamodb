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
     * @param Context\BatchGet $context
     * @return Collection
     */
    public function BatchGet(Context\BatchGet $context)
    {
        $items = new Collection();
        do {
            try {
                $result = $this->connection->batchGet($context);
                foreach ($result as $item) {
                    $items->add($item);
                }
            } catch (\Riverline\DynamoDB\Exception\ProvisionedThroughputExceededException $e) {
                // Continue
            }
        } while($result->more());

        return $items;
    }

    public function BatchWrite(Context\BatchWrite $context)
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
