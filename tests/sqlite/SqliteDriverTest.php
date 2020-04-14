<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Kristuff\Patabase\Database;
use PHPUnit\Framework\TestCase;

class SqliteDriverTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Kristuff\Patabase\Database
     */
    private static $db;

    public static function setUpBeforeClass()
    {
        self::$db = new Database(array('driver' => 'sqlite', 'database' => ':memory:'));
    }
 
    /**
     * @expectedException Kristuff\Patabase\Exception\MissingArgException
     */
     public function testMissingRequiredParameter()
    {
        new Kristuff\Patabase\Driver\Sqlite\SqliteDriver(array());
    }

    public function testEscape()
    {
        $this->assertEquals('"a"', self::$db->getDriver()->escape('a'));
        $this->assertEquals('"a"."b"', self::$db->getDriver()->escape('a.b'));
        $this->assertEquals(array('"a"', '"b"'), self::$db->getDriver()->escapeList(array('a', 'b')));
    }
   
    public function testDriverMethods()
    {
        $this->assertTrue(self::$db->getDriver()->isForeignKeyEnabled());
    }

    public static function tearDownAfterClass()
    {
        self::$db = null;   
    }
}
