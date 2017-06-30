<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/ServerTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;

class PgsqlDatabaseDropTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Kristuff\Patabase\Server
     */
    private static $srv;

    public static function setUpBeforeClass()
    {
        $settings = [
            'driver'    => 'pgsql', 
            'hostname'  => 'localhost', 
            'username'  => 'postgres',
            'password'  => ''
        ];
        self::$srv = new Server($settings);
    }

    public function testExists()
    {
        $this->assertTrue(self::$srv->databaseExists('patabaseTest'));
    }

    public function testDropDatabase()
    {
        $this->assertTrue(self::$srv->dropDatabase('patabaseTest'));
    }

    public function testDropUser()
    {
        $this->assertTrue(self::$srv->dropUser('tototo'));
    }

    public function testExistAfterDrop()
    {
        $this->assertFalse(self::$srv->databaseExists('patabaseTest'));
    }

    public static function tearDownAfterClass()
    {
        self::$srv = null;   
    }

}
