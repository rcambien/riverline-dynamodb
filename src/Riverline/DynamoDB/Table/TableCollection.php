<?php

namespace Riverline\DynamoDB\Table;

/**
 * @class
 */
class TableCollection implements \IteratorAggregate, \Countable
{
    /**
     * The table name collection
     * @var array
     */
    protected $tables = array();

    /**
     * The previous request last table
     * @var string|null
     */
    protected $lastTable;

    /**
     * @param string|null $lastTable The previous request last table
     */
    function __construct($lastTable = null)
    {
        $this->lastTable = $lastTable;
    }

    /**
     * Return the previous request last table
     * @return null|string
     */
    public function getLastTable()
    {
        return $this->lastTable;
    }

    /**
     * Return true if the previous request has more tables to retreive
     * @return bool
     */
    public function more()
    {
        return !empty($this->lastTable);
    }

    /**
     * Add an table name to the collection
     * @param string $table
     */
    public function add($table)
    {
        $this->table[] = $table;
    }

    /**
     * Remove an table name off the beginning of the collection
     * @return string
     */
    public function shift()
    {
        return array_shift($this->tables);
    }

    /**
     * @see \IteratorAggregate
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->tables);
    }

    /**
     * @see \Countable
     * @return int
     */
    public function count()
    {
        return count($this->tables);
    }
}