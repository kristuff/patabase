<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Kristuff\Patabase;
use Kristuff\Patabase\Database;
use Kristuff\Patabase\Server;
use Kristuff\Patabase\Table;
use Kristuff\Patabase\SqlException;
use Kristuff\Patabase\Query\CreateTable;
use Kristuff\Patabase\Query\Update;
use PHPUnit\Framework\TestCase;


abstract class DatabaseInjectionTest extends TestCase
{
    /**
     * @var Kristuff\Patabase\Database
     */
    protected static $db;


    /**
     * @var Kristuff\Patabase\Server
     */
    protected static $srv;

   
    protected static function createTables()
    {
          
        self::$db->table('test')
                    ->create()
                    ->column('id', 'int', 'pk', 'ai')
                    ->column('name', 'varchar(255)')
                    ->execute();

        self::$db->table('test_injection')
                ->create()
                ->column('id', 'int', 'pk', 'ai')
                ->column('name', 'varchar(255)')
                ->execute();

       self::$db->insert('test')
                ->setValue('name', 'John')
                ->execute();
            
       self::$db->insert('test')
                ->setValue('name', 'John`; DROP table test_injection;`')
                ->execute();

        self::$db->insert('test')
                ->setValue('name', 'John"; DROP table test_injection;"')
                ->execute();

        self::$db->insert('test')
                ->setValue('name', "John'; DROP table test_injection;'")
                ->execute();

    }

    public function testInjectionDropTable()
    {
       $this->assertTrue( self::$db->table('test_injection')
                                   ->exists());

    }

}