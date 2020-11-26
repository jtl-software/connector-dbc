<?php

use Jtl\Connector\Dbc\Console\Command\UpdateDatabaseSchemaCommand;
use Jtl\Connector\Dbc\DbManager;
use Symfony\Component\Console\Application;

$autoloadFiles = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(dirname(dirname(__DIR__))) . '/autoload.php',
];

$autoloaderFound = false;
foreach ($autoloadFiles as $autoloadFile) {
    if (is_file($autoloadFile)) {
        require_once $autoloadFile;
        $autoloaderFound = true;
        break;
    }
}

if (!$autoloaderFound) {
    throw new \Exception('Composer autoload.php not found. Did you run "composer install"?');
}

$dbParamsFiles = [
    dirname(__DIR__) . '/db-config.php',
    dirname(dirname(dirname(dirname(__DIR__)))) . '/db-config.php',
];

$dbParams = null;
foreach ($dbParamsFiles as $dbParamsFile) {
    if (file_exists($dbParamsFile)) {
        $dbParams = require_once $dbParamsFile;
        break;
    }
}

if (is_null($dbParams)) {
    throw new \Exception('db-config.php file not found');
}

if (is_array($dbParams)) {
    $dbManager = DbManager::createFromParams($dbParams);
} elseif ($dbParams instanceof \PDO) {
    $dbManager = DbManager::createFromPDO($dbParams);
} else {
    throw new \Exception('Database params do not have a valid format');
}

$dbTablesFiles = [
    dirname(__DIR__) . '/db-tables.php',
    dirname(dirname(dirname(dirname(__DIR__)))) . '/db-tables.php',
];

foreach ($dbTablesFiles as $dbTablesFile) {
    if (file_exists($dbTablesFile)) {
        $callable = require_once $dbTablesFile;
        if (!is_callable($callable)) {
            throw new \Exception(sprintf('%s did not return a callable function', $dbTablesFile));
        }

        $callable($dbManager);
    }
}

$cli = new Application('JTL Database Connectivity Console');
$cli->add(new UpdateDatabaseSchemaCommand($dbManager));
$cli->run();