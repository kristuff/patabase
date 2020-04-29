<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseInjectionTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;

class MysqlInjectionTest extends DatabaseInjectionTest
{
    public function setUpBeforeClass() : void
    {   
        $settings = [
            'driver'    => 'mysql', 
            'hostname'  => 'localhost', 
            'username'  => 'root',
            'password'  => ''
        ];
      
        $this->srv = new Server($settings);
        $this->srv->createDatabaseAndUser('patabaseTestInjection','tutu', 'pass');
     
        $dbsettings = [
            'driver'    => 'mysql', 
            'hostname'  => 'localhost', 
            'database'  => 'patabaseTestInjection',
            'username'  => 'tutu',
            'password'  => 'pass'
        ];
        $this->db = new Database($dbsettings);
        $this->createTables();
    }

    public function testInjectionDropTable()
    {
    


       // John’; DROP table users_details;’       
       $this->db->insert('test')
                ->setValue('name', 'John')
                ->execute();
            
       $this->db->insert('test')
                ->setValue('name', 'John`; DROP table test_injection;`')
                ->execute();

        $this->db->insert('test')
                ->setValue('name', "John'; DROP table test_injection;'")
                ->execute();

       $this->assertTrue( $this->db->table('test_injection')
                                   ->exists());


      

    }

    public function testDebug1()
    {
    
       // debug
       $this->assertEquals('', $this->db->select('name')->from('test')->getAll('JSON'));

    }

    public function testDebug2()
    {
    
       // debug
       $this->assertEquals('', json_encode($this->db->getTables()));

    }

}
