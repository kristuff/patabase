<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseInjectionTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;

class MysqlInjectionTest extends DatabaseInjectionTest
{
    public static function setUpBeforeClass() : void
    {   
        $settings = [
            'driver'    => 'mysql', 
            'hostname'  => 'localhost', 
            'username'  => 'root',
            'password'  => ''
        ];
      
        self::$srv = new Server($settings);
        self::$srv->createDatabaseAndUser('patabaseTestInjection','tutu', 'pass');
     
        $dbsettings = [
            'driver'    => 'mysql', 
            'hostname'  => 'localhost', 
            'database'  => 'patabaseTestInjection',
            'username'  => 'tutu',
            'password'  => 'pass'
        ];
        self::$db = new Database($dbsettings);
        self::createTables();
    }


}
