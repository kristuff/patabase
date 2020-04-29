<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseInjectionTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;

class PgsqlInjectionTest extends DatabaseInjectionTest
{
    public static function setUpBeforeClass() : void
    {   
      
        $settings = [
            'driver'    => 'pgsql', 
            'hostname'  => 'localhost', 
            'username'  => 'postgres',
            'password'  => ''
        ];
      
        $srv = new Server($settings);
        $srv->createDatabaseAndUser('patabaseTestInjection','tutu', 'pass');
     
        $dbsettings = [
            'driver'    => 'pgsql', 
            'hostname'  => 'localhost', 
            'database'  => 'patabaseTestInjection',
            'username'  => 'tutu',
            'password'  => 'pass'
        ];
        self::$db = new Database($dbsettings);
        $this->createTables();
  
    }

    public function testInjectionDropTable()
    {
        self::$db->insert('test')
        ->setValue('name', 'John')
        ->execute();
      
        self::$db->insert('test')
                ->setValue('name', 'John`; DROP table test_injection;`')
                ->execute();
                    
        self::$db->insert('test')
                                    ->setValue('name', "John'; DROP table test_injection;'")
                                    ->execute();
                    

       $this->assertTrue( self::$db->table('test_injection')
                                   ->exists());


    }

    public function testDebug1()
    {
    
       // debug
       $this->assertEquals('', self::$db->select('name')->from('test')->getAll('JSON'));

    }

    public function testDebug2()
    {
    
       // debug
       $this->assertEquals('', json_encode(self::$db->getTables()));

    }


}
