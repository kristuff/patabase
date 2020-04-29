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

  

    public function testDebug1()
    {
    
       // debug
       $this->assertEquals('', self::$db->select()->from('test')->getAll('JSON'));

    }

    public function testDebug2()
    {
    
       // debug
       $this->assertEquals('', json_encode(self::$db->getTables()));

    }

}
