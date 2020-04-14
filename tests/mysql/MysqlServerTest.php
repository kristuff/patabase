<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/ServerTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;
use PHPUnit\Framework\TestCase;

class MysqlServerTest extends ServerTest
{

    public static function setUpBeforeClass() : void
    {
        $settings = [
            'driver'    => 'mysql', 
            'hostname'  => 'localhost', 
            'username'  => 'root',
            'password'  => ''
        ];
        self::$srv= new Server($settings);
        self::$srv->dropDatabase('patabase', true);
        self::$srv->dropDatabase('patabaseTest', true);
        self::$srv->dropUser('toto', true);
        self::$srv->dropUser('tototo', true);
    }
    
    public static function tearDownAfterClass()
    {
        self::$srv =  NULL;  
    }
}
