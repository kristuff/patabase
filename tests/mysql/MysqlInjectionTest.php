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
      
        $srv = new Server($settings);
        $srv->createDatabaseAndUser('patabaseTestInjection','tutu', 'pass');
     
        $dbsettings = [
            'driver'    => 'mysql', 
            'hostname'  => 'localhost', 
            'database'  => 'patabaseTestInjection',
            'username'  => 'tutu',
            'password'  => 'pass'
        ];
        self::$db = new Database($dbsettings);

    }

    public function testInjectionDropTable()
    {
        $this->assertTrue( self::$db->table('test')
                                    ->create()
                                    ->ifNotExists()
                                    ->column('id', 'int', 'pk')
                                    ->column('name', 'varchar(255)')
                                    ->execute());

        $this->assertTrue( self::$db->table('test_injection')
                                    ->create()
                                    ->ifNotExists()
                                    ->column('id', 'int', 'pk')
                                    ->column('name', 'varchar(255)')
                                    ->execute());


       // John’; DROP table users_details;’                                    
       $this->assertTrue( self::$db->insert('test')
                                    ->setValue('name', 'John`; DROP table test_injection;`')
                                    ->execute());

       $this->assertTrue( self::$db->table('test_injection')
                                   ->exists());


    }


}
