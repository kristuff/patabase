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
        
        self::$db->table('test')
        ->create()
        ->ifNotExists()
        ->column('id', 'int', 'pk')
        ->column('name', 'varchar(255)')
        ->execute();

     self::$db->table('test_injection')
        ->create()
        ->ifNotExists()
        ->column('id', 'int', 'pk')
        ->column('name', 'varchar(255)')
        ->execute();
    }

    public function testInjectionDropTable()
    {
    


       // John’; DROP table users_details;’                                    
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
       $this->assertEquals('', json_encode(self::$db->select('name')->from('test')->getAll('JSON')));

    }

    public function testDebug2()
    {
    
       // debug
       $this->assertEquals('', json_encode(self::$db->getTables()));

    }

}
