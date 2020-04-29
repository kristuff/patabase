<?php

require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../base/DatabaseInjectionTest.php';

use Kristuff\Patabase\Database;
use Kristuff\Patabase\Driver\Sqlite;
use PHPUnit\Framework\TestCase;

class SqliteInjectionTest extends DatabaseInjectionTest
{
    public static function setUpBeforeClass() : void
    {   
        self::$db = new Database(array('driver' => 'sqlite', 'database' => ':memory:'));

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
                                    ->value('name', 'John"; DROP table test_injection;"')
                                    ->execute());

       $this->assertTrue( self::$db->table('test_injection')
                                    ->exists());


    }


}
