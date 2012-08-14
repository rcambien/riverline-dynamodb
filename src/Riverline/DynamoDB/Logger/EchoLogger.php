<?php

namespace Riverline\DynamoDB\Logger;

class EchoLogger implements Logger
{
    /**
     * @var int
     */
    protected $minLevel;

    /**
     * @param int $minLevel
     */
    public function __construct($minLevel = Logger::INFO)
    {
        $this->minLevel = $minLevel;
    }

    public function log($message, $level = Logger::INFO)
    {
        if ($level >= $this->minLevel) {

            switch($level) {
                case Logger::DEBUG:
                    $level = 'DEBUG';
                    break;
                case Logger::INFO:
                    $level = 'INFO';
                    break;
                case Logger::WARN:
                    $level = 'WARN';
                    break;
                case Logger::ERROR:
                    $level = 'ERROR';
                    break;
                default:
                    $level = 'UNKNOW';
            }

            echo "[$level] $message\n";
        }
    }
}
