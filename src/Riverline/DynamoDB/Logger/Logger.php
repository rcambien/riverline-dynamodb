<?php

namespace Riverline\DynamoDB\Logger;

interface Logger
{
    const DEBUG = 0;
    const INFO  = 1;
    const WARN  = 2;
    const ERROR = 3;

    public function log($message, $level = Logger::INFO);
}