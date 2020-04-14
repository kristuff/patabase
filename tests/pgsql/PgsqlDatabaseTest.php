<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;
use PHPUnit\Framework\TestCase;

class PgsqlDatabaseTest extends DatabaseTest
{
    public static function setUpBeforeClass() : void
    {
        self::$sqlJoin       = 'SELECT "name" AS "userName", "user_role"."role_name" AS "userRole" FROM "user" LEFT OUTER JOIN "user_role" ON "user"."role"="user_role"."role_id"';
        self::$sqlSubSelect  = 'SELECT "name" AS "userName", "role" AS "userRole", (SELECT COUNT(*) AS "count_role" FROM "user_role") AS "test_count" FROM "user"';

        $settings = [
            'driver'    => 'pgsql', 
            'hostname'  => 'localhost'
        ];
        $settings['database'] = 'patabaseTest';
        $settings['username'] = 'tototo';
        $settings['password'] = 'passss';

        self::$db = new Database($settings);
    }
   
    public static function tearDownAfterClass()
    {
        self::$db = null;
    }
}