<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/ServerTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;
use PHPUnit\Framework\TestCase;

class MysqlDatabaseDropTest extends TestCase
{

    /**
     * @var Kristuff\Patabase\Server
     */
    private static $srv;

    public static function setUpBeforeClass() : void
    {
        $settings = [
            'driver'    => 'mysql', 
            'hostname'  => 'localhost', 
            'username'  => 'root',
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

    public static function tearDownAfterClass() : void
    {
        self::$srv = null;   
    }

}
