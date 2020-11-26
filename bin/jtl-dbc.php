<?php

use Jtl\Connector\Dbc\Console\Command\UpdateDatabaseSchemaCommand;
use Jtl\Connector\Dbc\DbManager;
use Symfony\Component\Console\Application;

$autoloadFiles = [
    dirname(__DIR__) . '/vendor/autoload.php',
    dirname(dirname(dirname(__DIR__))) . '/autoload.php',
];

$found = false;
foreach ($autoloadFiles as $autoloadFile) {
    if (is_file($autoloadFile)) {
        require_once $autoloadFile;
        $found = true;
        break;
    }
}

if (!$found) {
    throw new \Exception('Composer autoload.php not found. Did you run "composer install"?');
}

$dbParamsFile = dirname(__DIR__) . '/db-config.php';

if (!file_exists($dbParamsFile)) {
    throw new \Exception('db-config.php file not found');
}

$dbParams = require_once $dbParamsFile;

if (is_array($dbParams)) {
    $dbManager = DbManager::createFromParams($dbParams);
} elseif ($dbParams instanceof \PDO) {
    $dbManager = DbManager::createFromPDO($dbParams);
} else {
    throw new \Exception('Database params do not have a valid format');
}

$dbTablesFile = dirname(__DIR__) . '/db-tables.php';

if (file_exists($dbTablesFile)) {
    $callable = require_once $dbTablesFile;
    if (!is_callable($callable)) {
        throw new \Exception('db-tables.php did not return a callable function');
    }
    $callable($dbManager);
}

$cli = new Application('JTL Database Connectivity Console');
$cli->add(new UpdateDatabaseSchemaCommand($dbManager));

$cli->run();