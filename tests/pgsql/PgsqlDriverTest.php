<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Kristuff\Patabase\Server;

class PgsqlDriverTest extends PHPUnit_Framework_TestCase
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
        self::$srv= new Server($settings);
    }
 
    /**
     * @expectedException Kristuff\Patabase\Exception\MissingArgException
     */
     public function testMissingRequiredParameter()
    {
        new Kristuff\Patabase\Driver\Mysql\MysqlDriver(array());
    }

    public function testEscape()
    {
        $this->assertEquals('"a"', self::$srv->getDriver()->escape('a'));
        $this->assertEquals('"a"."b"', self::$srv->getDriver()->escape('a.b'));
        $this->assertEquals(array('"a"', '"b"'), self::$srv->getDriver()->escapeList(array('a', 'b')));
    }

    public static function tearDownAfterClass()
    {
        self::$srv = null;   
    }
}