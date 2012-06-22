<?php

$vendorDir = __DIR__ . '/../vendor';

if (!@include(__DIR__ . '/../vendor/autoload.php')) {
    die("You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install
");
}

// Create a uniq ID to avoid ID collision between test
define('ITEM_ID', crc32(uniqid()));