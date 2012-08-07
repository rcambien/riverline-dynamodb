<?php

$vendorDir = __DIR__ . '/../vendor';

if (!@include(__DIR__ . '/../vendor/autoload.php')) {
    die("You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install
");
}

// Create a versioned table name to avoid collision between php 5.3 and 5.4 tests
$version = floatval(phpversion());
define('DY_TABLE_TMP_VER', DY_TABLE_TMP.PHP_MAJOR_VERSION.PHP_MINOR_VERSION);

// Create a uniq ID to avoid ID collision between tests
define('ITEM_ID', crc32(uniqid()));

