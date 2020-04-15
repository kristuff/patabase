<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Driver\Sqlite;
use PHPUnit\Framework\TestCase;

class SqliteDatabaseTest extends DatabaseTest
{

    private static $writablePath = "/tmp/";

    public static function setUpBeforeClass() : void
    {   
        self::$sqlJoin       = 'SELECT "name" AS "userName", "user_role"."role_name" AS "userRole" FROM "user" LEFT OUTER JOIN "user_role" ON "user"."role"="user_role"."role_id"';
        self::$sqlSubSelect  = 'SELECT "name" AS "userName", "role" AS "userRole", (SELECT COUNT(*) AS "count_role" FROM "user_role") AS "test_count" FROM "user"';
        self::$db = new Database(array('driver' => 'sqlite', 'database' => ':memory:'));
        //$this->sqlCreateTable = 'CREATE TABLE  "testTable" ("id" INTEGER  NOT NULL PRIMARY KEY, "name" string(50) NOT NULL, "opt" string(50) NULL)';
    }
   
    public function testDerivedClass()
    {
        $path = self::$writablePath . 'patabasetest.db';

        $db = new Database(array('driver' => 'sqlite', 'database' => $path)); 
        $db = null;
        $db = new Sqlite\SqliteDatabase(array('driver' => 'sqlite', 'database' => $path)); 
        $this->assertTrue($db->isForeignKeyEnabled());
        $db = null;
        $db = Sqlite\SqliteDatabase::createInstance($path);
        $this->assertTrue($db->isForeignKeyEnabled());
        $db = null;
        $db = Sqlite\SqliteDatabase::createMemoryInstance();
        $this->assertTrue($db->isForeignKeyEnabled());
        $db = null;
    }

    public static function tearDownAfterClass() : void
    {
        self::$db = null;   
    }

}
