<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseInjectionTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Driver\Sqlite;
use PHPUnit\Framework\TestCase;

class SqliteInjectionTest extends DatabaseInjectionTest
{
    public static function setUpBeforeClass() : void
    {   
        self::$db = new Database(array('driver' => 'sqlite', 'database' => ':memory:'));
        self::createTables();
  
    }
}
