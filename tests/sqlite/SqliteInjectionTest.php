<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseInjectionTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Driver\Sqlite;
use PHPUnit\Framework\TestCase;

class SqliteInjectionTest extends DatabaseInjectionTest
{
    public function setUpBeforeClass() : void
    {   
        $this->db = new Database(array('driver' => 'sqlite', 'database' => ':memory:'));
        $this->createTables();
  
    }

    public function testInjectionDropTable()
    {
     
       // John’; DROP table users_details;’       
       $this->db->insert('test')
       ->setValue('name', 'John')
       ->execute();

       $this->assertTrue( $this->db->insert('test')
                                    ->setValue('name', 'John"; DROP table test_injection;"')
                                    ->execute());

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
