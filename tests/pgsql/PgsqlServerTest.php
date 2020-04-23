<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/ServerTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;
use PHPUnit\Framework\TestCase;

class PgsqlServerTest extends ServerTest
{

    public static function setUpBeforeClass() : void
    {
        $settings = [
            'driver'    => 'pgsql', 
            'hostname'  => 'localhost',     
            'username'  => 'postgres',
            'password'  => ''
        ];
        self::$srv = new Server($settings);
        self::$srv->dropDatabase('patabase', true);
        self::$srv->dropDatabase('patabaseTest', true);
        self::$srv->dropUser('toto', true);
        self::$srv->dropUser('tototo', true);
    }

    public static function tearDownAfterClass() : void
    {
        self::$srv =  NULL;  
    }

}
