<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Kristuff\Patabase;
use Kristuff\Patabase\Database;
use Kristuff\Patabase\Table;
use Kristuff\Patabase\SqlException;
use Kristuff\Patabase\Query\CreateTable;
use Kristuff\Patabase\Query\Update;
use PHPUnit\Framework\TestCase;


abstract class DatabaseTest extends TestCase
{

    /**
     * @var Kristuff\Patabase\Database
     */
    protected static $db;
    protected static $sqlJoin = ''; 
    protected static $sqlSubSelect = '';

    /**
     * @expectedException Kristuff\Patabase\Exception\MissingArgException
     */
     public function testMissingRequiredParameter()
    {
        new Database(array());
    }

    public function testTableCreate()
    {
        $this->assertTrue( self::$db->table('test')
            ->create()
            ->ifNotExists()
             ->column('id', 'int', 'pk')
             ->execute());
    
        $this->assertTrue( self::$db->tableExists('test'));
    
        $query = self::$db->table('test')
            ->create()
             ->column('id', 'int', 'pk');

        $this->assertFalse($query->execute()); 
        $this->assertTrue($query->hasError()); 
    }

    public function testTablesList()
    {
        $tables = self::$db->getTables();
        $this->assertEquals(1, count($tables));
        $this->assertTrue(in_array('test', $tables));
    }
    
    public function testTableExists()
    {
        $this->assertFalse(self::$db->tableExists('notExistingTable'));
        $this->assertFalse(self::$db->table('notExistingTable')->exists());

        $this->assertTrue( self::$db->table('test')
            ->create()
            ->ifNotExists()
             ->column('id', 'int', 'pk')
             ->execute()); 

        $this->assertTrue( self::$db->tableExists('test'));
        $this->assertTrue( self::$db->table('test')->exists());
        $this->assertFalse( self::$db->tableExists('testXXX'));
        $this->assertFalse( self::$db->table('testXXX')->exists());
    }

    public function testTableRename()
    {
        $this->assertTrue( self::$db->createTable('test1')
             ->ifNotExists()
             ->column('id', 'int', 'pk')
             ->execute()); 

        $this->assertTrue( self::$db->renameTable('test1', 'test_1'));
        $this->assertTrue( self::$db->tableExists('test_1'));
        $this->assertFalse( self::$db->renameTable('XOXO', 'test_1'));

        $this->assertTrue( self::$db->createTable('test2')
            ->ifNotExists()
             ->column('id', 'int', 'pk')
             ->execute()); 

        $this->assertTrue( self::$db->table('test2')->rename('test_2'));
        $this->assertTrue( self::$db->table('test_2')->exists());
        $this->assertEquals(self::$db->table('test_2')->name(),'test_2');
    }

    public function testTableDrop()
    {
        $this->assertTrue(self::$db->createTable('test1')
             ->ifNotExists()
             ->column('id', 'int', 'pk')
             ->execute());
              
        $this->assertTrue(self::$db->dropTable('test1'));
        $this->assertFalse(self::$db->dropTable('test1'));
        $this->assertTrue(self::$db->hasError());

        $this->assertTrue(self::$db->createTable('test2')
             ->ifNotExists()
             ->column('id', 'int', 'pk')
             ->execute());

        $this->assertTrue(self::$db->table('test2')->drop());
        $this->assertFalse(self::$db->table('test2')->drop());
        $this->assertTrue(self::$db->hasError());
    }

    public function testLastIdWithError()
    {
        $createTable = self::$db->createTable('testIdError')
             ->column('name', 'varchar(50)', 'Not NULL');

        $this->assertTrue($createTable->execute());

        $insert = self::$db->table('testIdError')->insert(array('name'=>'One'));
        $this->assertFalse($insert->lastId());

        //$this->assertTrue($insert->execute());
        //$this->assertEquals(0, $insert->lastId());
    }
   
    public function testLastId()
    {
        $query = self::$db->createTable('testId')
             ->column('id',   'int', 'pk', 'AI')
             ->column('name', 'varchar(50)', 'Not NULL')
             ->column('opt',  'varchar(50)', 'NULL');

        $this->assertTrue($query->execute());
        $this->assertFalse($query->execute());
        $this->assertTrue($query->hasError());

        $insert1 = self::$db->table('testId')->insert(array('name'=>'One'));
        $insert2 = self::$db->table('testId')->insert(array('name'=>'Two', 'opt' => 'option2'));

        $this->assertTrue($insert1->execute());
        $this->assertEquals(1, $insert1->lastId());
        $this->assertTrue($insert2->execute());
        $this->assertEquals(2, $insert2->lastId());

        $this->assertEquals('[{"id":1},{"id":2}]', self::$db->table('testId')->select('id')->getAll('json'));
        $this->assertEquals('[{"id":1}]'           , self::$db->table('testId')->select('id')->where()->isNull('opt')->getAll('json'));
        $this->assertEquals('[{"id":2}]'           , self::$db->table('testId')->select('id')->where()->notNull('opt')->getAll('json'));
    }

   


    public function testCreateWithUniqueValues()
    {
        $createTable = self::$db->createTable('testUnique')
             ->column('id',   'int', 'pk')
             ->column('name', 'varchar(50)', 'NOT NULL', 'UNIQUE');
        $created = $createTable ->execute();

        // for debug
        if (! $created) {
            $this->assertEquals('', $createTable->errorMessage());
        }

        $this->assertTrue($created);
        
        $insert = self::$db->insert('testUnique')->prepare('id', 'name');

        // first should pass
        $this->assertTrue($insert->values(['id' => 1, 'name' => 'xoxo'])->execute());
        $this->assertFalse($insert->hasError());

        // second with same name should failed
        $this->assertFalse($insert->values(['id' => 2, 'name'=> 'xoxo'])->execute());
        $this->assertTrue($insert->hasError());

    }
  
     public function testCreateWithDefaultValues()
    {
        $query = self::$db->createTable('testDefault')
             ->column('id',       'int',           'PK',       'AI')
             ->column('name',     'varchar(50)',   'NOT NULL', 'DEFAULT', 'xoxo')
             ->column('opt1',     'int',           'NOT NULL', 'DEFAULT', 2 )
             ->column('opt2',     'real',          'NOT NULL', 'DEFAULT', 2.2 )
             ->column('foo',      'varchar(10)',   'NOT NULL')
             ->column('bool',     'bool',          'NOT NULL', 'DEFAULT', true) // see above
             ->column('bigint',   'bigint',        'NOT NULL', 'DEFAULT', 922337203685477580)
             ->column('smallint', 'smallint',      'NOT NULL', 'DEFAULT', 0);

        $created = $query->execute();
        // debug
        if (! $created) {
            $this->assertEquals('', $query->errorMessage());
        }
        $this->assertTrue($created);


        $insert = self::$db->table('testDefault')->insert()->prepare('foo');
        $this->assertTrue($insert->setValue('foo', 'bar')->execute());

        switch (self::$db->getDriverName()){
            case 'mysql':
                // bool returned as int
                $this->assertEquals('[{"id":1,"name":"xoxo","opt1":2,"opt2":2.2,"foo":"bar","bool":1,"bigint":922337203685477580,"smallint":0}]', 
                    self::$db->table('testDefault')->select()->whereEqual('id', 1)->getOne('json'));
                break;
  
                case 'pgsql':
                // bool returned as bool
                $this->assertEquals('[{"id":1,"name":"xoxo","opt1":2,"opt2":2.2,"foo":"bar","bool":true,"bigint":922337203685477580,"smallint":0}]', 
                    self::$db->table('testDefault')->select()->whereEqual('id', 1)->getOne('json'));
                break;

            case 'sqlite':
                // No bool type in sqlite: 
                //  with php 7.3                => retuns int  1
                //  with php 7.4 and php < 7.3  => retuns str  "TRUE" 
                $possibleResults = [
                    '[{"id":1,"name":"xoxo","opt1":2,"opt2":2.2,"foo":"bar","bool":"TRUE","bigint":922337203685477580,"smallint":0}]',
                    '[{"id":1,"name":"xoxo","opt1":2,"opt2":2.2,"foo":"bar","bool":1,"bigint":922337203685477580,"smallint":0}]'
                ];

                $queryResult = self::$db->table('testDefault')->select()->whereEqual('id', 1)->getOne('json');
                $ok = $possibleResults[0] === $queryResult ||
                      $possibleResults[1] === $queryResult;

                $this->assertTrue($ok);
                    
            case 'mssql':
                //TODO
                break;
        }


        $query = self::$db->createTable('testDefault2')
             ->column('id',    'int',          'PK',      'AUTO INCREMENT')
             ->column('null',  'varchar(10)',  'NULL',    'DEFAULT', 'NULL')
             ->column('foo',   'varchar(10)',  'NULL');

        $created = $query->execute();
        // debug
        if (! $created) {
            $this->assertEquals('', $query->errorMessage());
        }
        $this->assertTrue($created);

        $insert = self::$db->table('testDefault2')->insert()->prepare('foo');
        $this->assertTrue($insert->setValue('foo', 'bar')->execute());
        $this->assertEquals('[{"id":1,"null":null,"foo":"bar"}]', 
            self::$db->table('testDefault2')->select('id','null', 'foo')->whereEqual('id', 1)->getOne('json'));

        $this->assertTrue($insert->setValue('foo', null)->execute());
        $this->assertEquals('[{"id":2,"null":null,"foo":null}]', 
            self::$db->table('testDefault2')->select('id','null', 'foo')->whereEqual('id', 2)->getOne('json'));


    }
    public function testCreateTableWithTimestamp()
    {
        $query = self::$db->createTable('testDefault3')
                    ->column('id','int', 'PK','AUTO INCREMENT');

        switch (self::$db->getDriverName()){
            case 'mysql':
            case 'pgsql':
                $query->column('ct','timestamp', 'NULL',  'DEFAULT', 'CURRENT_TIMESTAMP');
                $this->assertTrue($query->execute());
                $this->assertNotNull(self::$db->table('testDefault3')->select('ct')->whereEqual('id', 1)->getColumn());
                break;
            case 'sqlite':
                $query->column('ct','datetime', 'NULL',  'DEFAULT', 'CURRENT_TIMESTAMP');
                $this->assertTrue($query->execute());
                $this->assertNotNull(self::$db->select('ct')->from('testDefault3')->whereEqual('id', 1)->getColumn());
                break;
            case 'mssql':
                //TODO
                break;
        }
    }

    public function testInsert()
    {

        $this->assertTrue(self::$db->table('user_role')->create()
            ->ifNotExists()
            ->column('role_id', 'int', 'pk')
            ->column('role_name', 'varchar(50)', 'NULL')
            ->execute()); 

        $insert = self::$db->table('user_role')->insert();
        $this->assertTrue($insert->values(array('role_id'=>1,'role_name'=> 'Guest'))->execute());
        $this->assertTrue($insert->prepare('role_id', 'role_name')->values(array('role_id'=>2,'role_name'=> 'Standard'))->execute());
        $this->assertTrue($insert->values(array('role_id'=>3,'role_name'=> 'Admin'))->execute());

        $query= self::$db->table('user')->create()->ifNotExists()
            ->column('id',   'int', 'pk')
            ->column('name', 'varchar(50)', 'NULL')
            ->column('age',  'int', 'NULL')
            ->column('role', 'int', 'fk', 'user_role', 'role_id')
            ->fk('fk_user_userrrole','role','user_role','role_id');
   
        $this->assertTrue($query->execute()); 

        $this->assertTrue(self::$db->table('user')->insert(array('id'=>1,'name'=> 'Bryan', 'age'=> 34, 'role' => '1'))->execute());
        $this->assertTrue(self::$db->table('user')->insert(array('id'=>2,'name'=> 'Steve', 'age'=> 32, 'role' => '2'))->execute());
        $this->assertTrue(self::$db->insert('user')->values(array('id'=>3,'name'=> 'John',  'age'=> 18, 'role' => '2'))->execute());
        $this->assertTrue(self::$db->table('user')->insert(array('id'=>4,'name'=> 'Chris', 'age'=> 38, 'role' => '3'))->execute());
        $this->assertTrue(self::$db->table('user')->insert(array('id'=>5,'name'=> 'Jane',  'age'=> 16, 'role' => '1'))->execute());
        $this->assertTrue(self::$db->insert('user')->values(array('id'=>6,'name'=> 'Jo',    'age'=> 32, 'role' => '2'))->execute());
        $this->assertTrue(self::$db->table('user')->insert(array('id'=>7,'name'=> 'Bryan', 'age'=> 27, 'role' => '1'))->execute());
        $this->assertTrue(self::$db->table('user')->insert(array('id'=>8,'name'=> 'Bryan', 'age'=> 27, 'role' => '2'))->execute());
   
        $this->assertFalse(self::$db->table('user')->insert(array('id'=>8,'name'=> 'Bryan', 'age'=> 27, 'role' => '2'))->execute());
  
    }

    public function testSelect()
    {
        // simple
        $this->assertEquals(self::$db->table('user')->select()->getOne('json'), '[{"id":1,"name":"Bryan","age":34,"role":1}]');
        $this->assertEquals(self::$db->table('user')->select('id', 'name')->getOne('json'), '[{"id":1,"name":"Bryan"}]');
    }

    public function testSelectAlias()
    {
        // alias
        $expected = '[{"AliasForName":"Bryan","AliasForAge":34}]';
        $this->assertEquals(self::$db->table('user')->select(array('name' => 'AliasForName', 'age' => 'AliasForAge'))->getOne('json'), $expected);
        $this->assertEquals(self::$db->table('user')->select()->columns(array('name' => 'AliasForName', 'age' => 'AliasForAge'))->getOne('json'),$expected);
        $this->assertEquals(self::$db->table('user')->select()->column('name', 'AliasForName')->column('age', 'AliasForAge')->getOne('json'),$expected); 
    }

    public function testSelectDistinct()
    {
        // disctinct
        $this->assertEquals(6, count(self::$db->table('user')->select('name')->distinct()->getAll())); 
        $this->assertEquals(6, count(self::$db->table('user')->select('name')->distinct()->getAll()));
        $this->assertEquals(7, count(self::$db->table('user')->select()->distinct()->columns('name', 'age')->getAll()));
        $this->assertEquals(7, count(self::$db->table('user')->select('name', 'age')->distinct()->getAll()));
    }

    public function testSelectCount()
    {
        // count
        $this->assertEquals(self::$db->table('user')->select()->count('user_number')->getOne('json'), '[{"user_number":8}]');
    }

    public function testSelectSum()
    {
        //sum
        $this->assertEquals(self::$db->table('user')->select()->sum('age', 'total_age')->getOne('json'), '[{"total_age":224}]');
    }

    public function testSelectMin()
    {
        //sum
        $this->assertEquals(self::$db->table('user')->select()->min('age', 'min_age')->getOne('json'), '[{"min_age":16}]');
    }

    public function testSelectMax()
    {
        //sum
        $this->assertEquals(self::$db->table('user')->select()->max('age', 'max_age')->getOne('json'), '[{"max_age":34}]');
    }

    public function testSelectJoin()
    {
        $tableCustomer = self::$db->table('customer');
        $this->assertTrue($tableCustomer->create()
            ->column('customerId',         'int',        'PK')
            ->column('customerName',       'varchar(50)', 'NOT NULL')
            ->execute()); 

        $tableOrder  = self::$db->table('order');
        $createTable = $tableOrder->create()
            ->column('orderId',     'int', 'pk')
            ->column('customerId',  'int', 'NOT NULL')
            ->column('orderDate',   'varchar(10)', 'NOT NULL')
            ->fk('fk_order_customer','customerId', 'customer','customerId');
        $created = $createTable->execute();
        
        // debug
        if (! $created) {
            $this->assertEquals('', $createTable->errorMessage());
        }

        $insert = $tableCustomer->insert()->prepare('customerId','customerName');
        $this->assertTrue($insert->values(array('customerId' => 1, 'customerName' => 'customerB'))->execute());
        $this->assertTrue($insert->values(array('customerId' => 2, 'customerName' => 'customerZ'))->execute());
        $this->assertTrue($insert->values(array('customerId' => 3, 'customerName' => "customerA"))->execute());
        
        // wrong column
        $insert = $tableOrder->insert()->prepare('orderId','customerId', 'orderDate','shipperId');
        $this->assertFalse($insert->execute());

        $insert = $tableOrder->insert()->prepare('orderId','customerId', 'orderDate');
        $this->assertTrue($insert->values(array('orderId' => 10308,  'customerId' => 2, 'orderDate' => "2016-09-18"))->execute());
        $this->assertTrue($insert->values(array('orderId' => 10309,  'customerId' => 1, 'orderDate' => "2016-09-20"))->execute());
        $this->assertTrue($insert->values(array('orderId' => 10310,  'customerId' => 1, 'orderDate' => "2016-10-04"))->execute());

    }

    /**
     * @depends testSelectJoin
     */
    public function testCountInSubQuery()
    {
      //prepare query
      $query = self::$db->select('customerName')->from('customer')->orderBy('customerId');

        // sub query to get order number for given customer 
        $query->select('orderNumber')
            ->count('orderNumber')
            ->from('order')
            ->whereEqual('order.customerId', Patabase\Constants::COLUMN_LITERALL . 'customer.customerId');

       // debug  $this->assertEquals('', $query->sql());
        $this->assertEquals('[{"customerName":"customerB","orderNumber":2},{"customerName":"customerZ","orderNumber":1},{"customerName":"customerA","orderNumber":0}]', $query->getAll('json'));
     
    }

    /**
     * @depends testSelectJoin
     */
    public function testSelectInnerJoin()
    {
        $query = self::$db->select()
                  ->column('customer.customerName')
                  ->column('order.orderId')
                  ->from('customer')
                  ->join('order', 'customerId', 'customer', 'customerId')
                  ->orderAsc('order.orderId');

        $this->assertEquals('[{"customerName":"customerZ","orderId":10308},{"customerName":"customerB","orderId":10309},{"customerName":"customerB","orderId":10310}]', $query->getAll('json'));
 
        $query = self::$db->select()
                  ->column('customer.customerName')
                  ->column('order.orderId')
                  ->from('customer')
                  ->innerJoin('order', 'customerId', 'customer', 'customerId')
                  ->orderAsc('order.orderId');

        $this->assertEquals('[{"customerName":"customerZ","orderId":10308},{"customerName":"customerB","orderId":10309},{"customerName":"customerB","orderId":10310}]', $query->getAll('json'));
 
    }
    
    /**
     * @depends testSelectJoin
     */
    public function testSelectRightJoin()
    {
        // right join
        $query = self::$db->select()
                  ->column('customer.customerName')
                  ->column('order.orderId')
                  ->from('order')
                  ->rightJoin('customer', 'customerId', 'order', 'customerId')
                  ->orderAsc('customer.customerName');

        // not supported in sqlite / 
        if (self::$db->getDriver()->getDriverName() === 'sqlite'){
            $this->assertEquals('[]', $query->getAll('json'));
        } else {
            $this->assertEquals('[{"customerName":"customerA","orderId":null},{"customerName":"customerB","orderId":10309},{"customerName":"customerB","orderId":10310},{"customerName":"customerZ","orderId":10308}]', $query->getAll('json'));
        }


    }

    /**
     * @depends testSelectJoin
     */
    public function testSelectLeftJoin()
    {

        // left join
        $query = self::$db->table('order')
                  ->select()
                  ->column('order.orderId')
                  ->column('order.orderDate')
                  ->column('customer.customerName')
                  ->from('order')
                  ->leftJoin('customer', 'customerId', 'order', 'customerId')
                  ->orderAsc('order.orderId');
        $this->assertEquals('[{"orderId":10308,"orderDate":"2016-09-18","customerName":"customerZ"},{"orderId":10309,"orderDate":"2016-09-20","customerName":"customerB"},{"orderId":10310,"orderDate":"2016-10-04","customerName":"customerB"}]', $query->getAll('json'));


        $query = self::$db->table('user')
                  ->select()
                  ->column('name', 'userName')
                  ->column('user_role.role_name', 'userRole')
                  ->leftJoin('user_role', 'role_id', 'user', 'role');
        $this->assertEquals(self::$sqlJoin, $query->sql());
        $this->assertEquals('[{"userName":"Bryan","userRole":"Guest"}]', $query->getOne('json'));
    }

    public function testSelectFullJoin()
    {
        $tableCustomer = self::$db->table('customer2');
        $this->assertTrue($tableCustomer->create()
            ->column('customerId',         'int',        'PK')
            ->column('customerName',       'varchar(50)', 'NOT NULL')
            ->execute()); 

        $tableOrder  = self::$db->table('order2');
        $createTable = $tableOrder->create()
            ->column('orderId',     'int', 'pk')
            ->column('customerId',  'int', 'NOT NULL')
            ->column('orderDate',   'varchar(10)', 'NOT NULL');
        //    ->fk('fk_order_customer','customerId', 'customer','customerId');
        $created = $createTable->execute();
        // debug
        if (! $created) {
            $this->assertEquals('', $createTable->errorMessage());
        }

        $insert = $tableCustomer->insert()->prepare('customerId','customerName');
        $this->assertTrue($insert->values(array('customerId' => 1, 'customerName' => 'customerB'))->execute());
        $this->assertTrue($insert->values(array('customerId' => 2, 'customerName' => 'customerZ'))->execute());
        $this->assertTrue($insert->values(array('customerId' => 3, 'customerName' => "customerA"))->execute());
        
        // wrong column
        $insert = $tableOrder->insert()->prepare('orderId','customerId', 'orderDate','shipperId');
        $this->assertFalse($insert->execute());

        $insert = $tableOrder->insert()->prepare('orderId','customerId', 'orderDate');
        $this->assertTrue($insert->values(array('orderId' => 10307,  'customerId' => 5, 'orderDate' => "2015-10-30"))->execute());
        $this->assertTrue($insert->values(array('orderId' => 10308,  'customerId' => 2, 'orderDate' => "2016-09-18"))->execute());
        $this->assertTrue($insert->values(array('orderId' => 10309,  'customerId' => 1, 'orderDate' => "2016-09-20"))->execute());
        $this->assertTrue($insert->values(array('orderId' => 10310,  'customerId' => 1, 'orderDate' => "2016-10-04"))->execute());

        $query = self::$db->select()
                  ->column('order2.orderId')
                  ->column('order2.orderDate')
                  ->column('customer2.customerName')
                  ->from('order2')
                  ->fullJoin('customer2', 'customerId', 'order2', 'customerId')
                  ->orderAsc('order2.orderId');

        // not supported in sqlite and mysql / 
        $driver = self::$db->getDriverName();
        if ($driver=== 'sqlite' || $driver=== 'mysql'){
            $this->assertEquals('[]', $query->getAll('json'));
        } else {
            $this->assertEquals('[{"orderId":10307,"orderDate":"2015-10-30","customerName":null},{"orderId":10308,"orderDate":"2016-09-18","customerName":"customerZ"},{"orderId":10309,"orderDate":"2016-09-20","customerName":"customerB"},{"orderId":10310,"orderDate":"2016-10-04","customerName":"customerB"},{"orderId":null,"orderDate":null,"customerName":"customerA"}]', $query->getAll('json'));
        }
    }

    public function testSelectSubSelect()
    {
        // sub select 
        $query = self::$db->table('user')->select();
        $query->column('name', 'userName');
        $query->column('role', 'userRole');
        $query->select('test_count')
             ->count('count_role')
             ->from('user_role');
        $this->assertEquals(self::$sqlSubSelect, $query->sql());
        $this->assertEquals('[{"userName":"Bryan","userRole":1,"test_count":3}]', $query->getOne('json'));
    }

    public function testOutputJson()
    {
        $query = self::$db->table('user')->select('id','name','age')->orderAsc('id')->limit(2);
        $json = '[{"id":1,"name":"Bryan","age":34},{"id":2,"name":"Steve","age":32}]';
        $jsonPP = '[
    {
        "id": 1,
        "name": "Bryan",
        "age": 34
    },
    {
        "id": 2,
        "name": "Steve",
        "age": 32
    }
]';
        $this->assertEquals($json, print_r($query->getAll('json'), TRUE));
        $this->assertEquals($jsonPP, print_r($query->getAll('jsonpp'), TRUE));
    }

    public function testOutputAssoObj()
    {
        $query = self::$db->table('user')->select('id','name','age')->orderAsc('id')->limit(2);
        
        $assoStr = array();
        $assoStr[] = array('id'=>'1','name'=>'Bryan', 'age' => '34');
        $assoStr[] = array('id'=>'2','name'=>'Steve', 'age' => '32');

        $assoInt = array();
        $assoInt[] = array('id'=>1,'name'=>'Bryan', 'age' => 34);
        $assoInt[] = array('id'=>2,'name'=>'Steve', 'age' => 32);

        switch (self::$db->getDriver()->getDriverName()){
            case 'sqlite':
                $this->assertEquals($assoStr, $query->getAll('assoc'));
                break;

            default:
                $this->assertEquals($assoInt, $query->getAll('assoc'));
        }
        $this->assertTrue(is_object($query->getAll('obj')[0]));
    }

    public function testOutputOneColumn()
    {
        $this->assertEquals('Bryan', self::$db->table('user')->select('name')->orderAsc('id')->limit(1)->getColumn());
        $this->assertEquals(NULL, self::$db->table('user')->select('name')->whereEqual('id', 222)->getColumn());
    }

    public function testOutputColumns()
    {
        $query = self::$db->table('user')->select('name')->orderAsc('id')->limit(2);
        $cols = array('Bryan', 'Steve');
        $this->assertEquals(print_r(json_encode($cols, JSON_PRETTY_PRINT), TRUE), 
                           print_r(json_encode($query->getAll('column'), JSON_PRETTY_PRINT), true));

    }

    /**
     * @expectedException Kristuff\Patabase\Exception\InvalidArgException
     */
    public function tesInvalidOutput()
    {
        self::$db->table('user')->select('name')->getAll('wrong');
    }

    public function testWhere()
    {
        $this->assertEquals(
            self::$db->table('user')->select('name')->where()
                    ->beginAnd()
                        ->greaterEqual('id', 2)
                        ->greaterEqual('age', 18)
                    ->closeAnd()
                    ->getAll('json'),
            self::$db->table('user')->select('name')
                    ->where()->greaterEqual('id', 2)
                    ->where()->greaterEqual('age', 18)
                    ->getAll('json'));

        // ...Steve
        $this->assertEquals('Steve', self::$db->table('user')->select('name')->where()->equal('id', 2)->getColumn());
        $this->assertEquals('Steve', self::$db->table('user')->select('name')->where()->equal('name', 'Steve')->getColumn());
        $this->assertEquals('Steve', self::$db->table('user')->select('name')->whereEqual('name', 'Steve')->where()->equal('age', 32)->getColumn());
        $this->assertEquals('Steve', self::$db->table('user')->select('name')->where()->notEqual('name', 'Bryan')->getColumn());

        //...Jane
        $this->assertEquals('Jane', self::$db->table('user')->select('name')->where()->lower('age', 18)->getColumn());
        $this->assertEquals('Jane', self::$db->table('user')->select('name')->where()->lowerEqual('age', 16)->getColumn());
        $this->assertEquals('Jane', self::$db->table('user')->select('name')->where()->beginOr()->lower('age', 17)->greater('age', 37)->closeOr()
                                 ->where()->greater('id', 4)->getColumn());

        //...Chris
        $this->assertEquals('Chris', self::$db->table('user')->select('name')->where()->greater('age', 35)->getColumn());
        $this->assertEquals('Chris', self::$db->table('user')->select('name')->where()->greaterEqual('age', 38)->getColumn());
        $this->assertEquals('Chris', self::$db->table('user')->select('name')->where()->like('name','Chr%')->getColumn());
        $this->assertEquals(4, count(self::$db->table('user')->select('name')->where()->notLike('name','%r%')->getAll()));
       
        //  [{"name":"Chris"},{"name":"Jane"}]
        $this->assertEquals('[{"name":"Chris"},{"name":"Jane"}]',
                self::$db->table('user')->select('name')->where()->beginOr()->lower('age', 17)->greater('age', 37)->closeOr()->getAll('json'));
                
        // [{"id":2,"name":"Steve"},{"id":3,"name":"John"},{"id":4,"name":"Chris"}]
        $this->assertEquals('[{"id":2,"name":"Steve"},{"id":3,"name":"John"},{"id":4,"name":"Chris"}]', 
            self::$db->table('user')->select('id', 'name')->where()->in('id', array(2,3,4))->getAll('json'));

        // [{"name":"Bryan"},{"name":"Jane"},{"name":"Jo"},{"name":"Bryan"},{"name":"Bryan"}]
        $this->assertEquals('[{"name":"Bryan"},{"name":"Jane"},{"name":"Jo"},{"name":"Bryan"},{"name":"Bryan"}]',
            self::$db->table('user')->select('name')->where()->notIn('id', array(2,3,4))->getAll('json'));

        $lineSteve = '[{"id":2,"name":"Steve","age":32,"role":2}]';
        $this->assertEquals($lineSteve, self::$db->table('user')->select()->where()->equal('id', 2)->getAll('json'));
        $this->assertEquals($lineSteve, self::$db->table('user')->select()->where()->equal('id', 2)->where()->equal('age', 32)->getAll('json'));
        $this->assertEquals($lineSteve, self::$db->table('user')->select()->where()->equal('name', 'Steve')->getAll('json'));
        $this->assertEquals($lineSteve, self::$db->table('user')->select()->where()->notEqual('name', 'Bryan')->getOne('json'));
        $this->assertEquals($lineSteve, self::$db->table('user')->select()->where()->notEqual('name', 'Bryan')->getOne('json'));

        $this->assertEquals($lineSteve, self::$db->select()->from('user')->where()->equal('id', 2)->getAll('json'));
        $this->assertEquals($lineSteve, self::$db->select()->from('user')->where()->equal('id', 2)->where()->equal('age', 32)->getAll('json'));
        $this->assertEquals($lineSteve, self::$db->select()->from('user')->where()->equal('name', 'Steve')->getAll('json'));
        $this->assertEquals($lineSteve, self::$db->select()->from('user')->where()->notEqual('name', 'Bryan')->getOne('json'));
        $this->assertEquals($lineSteve, self::$db->select()->from('user')->where()->notEqual('name', 'Bryan')->getOne('json'));
    }

    public function testOffset()
    {
          $this->assertEquals('[{"name":"Chris"}]', self::$db->table('user')->select()->column('name')
            ->groupBy('name')->orderAsc('name')->limit(1)->offset(1)->getAll('json'));
    }

    public function testOrderBy()
    {
        $this->assertEquals('[{"name":"Bryan"},{"name":"Chris"},{"name":"Jane"},{"name":"Jo"},{"name":"John"},{"name":"Steve"}]',
            self::$db->table('user')->select('name')->distinct()->orderAsc('name')->getAll('json'));
        $this->assertEquals('[{"name":"Steve"},{"name":"John"},{"name":"Jo"},{"name":"Jane"},{"name":"Chris"},{"name":"Bryan"}]',
            self::$db->table('user')->select('name')->distinct()->orderDesc('name')->getAll('json'));
        $this->assertEquals('[{"name":"Bryan","age":27},{"name":"Bryan","age":27},{"name":"Bryan","age":34},{"name":"Chris","age":38},{"name":"Jane","age":16},{"name":"Jo","age":32},{"name":"John","age":18},{"name":"Steve","age":32}]',
            self::$db->table('user')->select('name', 'age')->orderAsc('name')->orderAsc('age')->getAll('json'));

        $rand = 0.868;
        if (self::$db->getDriverName() === 'mysql'){
             $rand = rand();
        }

        $query1 = self::$db->table('user')->select('age')->orderRand()->getAll('json');
        $query2 = self::$db->table('user')->select('age')->orderRand()->getAll('json');

        // $querysql = self::$db->table('user')->select('age')->orderRand(2)->sql();
        // $this->assertEquals('', $querysql);
        $this->assertNotEquals($query1, $query2);
    }
    
    public function testGroupBy()
    {
        $this->assertEquals('[{"name":"Bryan"},{"name":"Chris"},{"name":"Jane"},{"name":"Jo"},{"name":"John"},{"name":"Steve"}]',
            self::$db->select('name')->from('user')->groupBy('name')->orderAsc('name')->getAll('json'));
        $this->assertEquals('[{"name":"Bryan"},{"name":"Chris"},{"name":"Jane"},{"name":"Jo"},{"name":"John"},{"name":"Steve"}]',
            self::$db->table('user')->select()->column('name')->groupBy('name')->orderAsc('name')->getAll('json'));
        
        // count / group by
        $this->assertEquals( '[{"name":"Bryan","number":3},{"name":"Chris","number":1},{"name":"Jane","number":1},{"name":"Jo","number":1},{"name":"John","number":1},{"name":"Steve","number":1}]', 
            self::$db->select('name')->from('user')->count('number')->groupBy('name')->orderAsc('name')->getAll('json'));
        $this->assertEquals( '[{"name":"Bryan","number":3},{"name":"Chris","number":1},{"name":"Jane","number":1},{"name":"Jo","number":1},{"name":"John","number":1},{"name":"Steve","number":1}]', 
            self::$db->table('user')->select()->column('name')->count('number')->groupBy('name')->orderAsc('name')->getAll('json'));
    }

    public function testUpdate()
    {
        $query = self::$db->table('user')->update(array('name' => 'Pat'))->where()->equal('id', 1);
        $this->assertInstanceOf(Update::class, $query);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $newName = self::$db->table('user')->select('name')->where()->equal('id', 1)->getColumn();
        $this->assertNotEquals($newName, 'pat');
        $this->assertEquals($newName, 'Pat');

        $query = self::$db->update('user')->whereEqual('id', 1)->prepare('name');
        $query->setValue('name' , 'BryanEE');
        $this->assertTrue($query->execute());
        
        $newName = self::$db->select('name')->from('user')->whereEqual('id', 1)->getColumn();
        


        $query->values(array('name' => 'Bryan'));
        $this->assertTrue($query->execute());
        
        $query = self::$db->table('user')->update()->increment('age')->where()->equal('id', 1);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $age = self::$db->table('user')->select('age')->where()->equal('id', 1)->getColumn();
        $this->assertEquals($age, 35);

        $query = self::$db->update('user')->increment('age', 10)->where()->equal('id', 1);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $age = self::$db->table('user')->select('age')->where()->equal('id', 1)->getColumn();
        $this->assertEquals($age, 45);

        $query = self::$db->table('user')->update()->decrement('age')->where()->equal('id', 1);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $age = self::$db->table('user')->select('age')->where()->equal('id', 1)->getColumn();
        $this->assertEquals($age, 44);

        $query = self::$db->table('user')->update()->decrement('age', 10)->where()->equal('id', 1);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $age = self::$db->table('user')->select('age')->where()->equal('id', 1)->getColumn();
        $this->assertEquals($age, 34);

    }

    public function testDelete()
    {
        $query = self::$db->table('user')->delete()->where()->equal('id', 1);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $query = self::$db->delete('user')->where()->equal('id', 2);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $query = self::$db->delete('user')->whereEqual('id', 3);
        $this->assertTrue($query->execute());
        $this->assertEquals($query->rowCount(), 1);

        $countquery = self::$db->select()->count('total')->from('user');
        $this->assertEquals($countquery->getColumn(), 5);
        
        self::$db->beginTransaction();
        $query1 = self::$db->delete('user')->whereEqual('id', 4);
        $query2 = self::$db->delete('user')->whereEqual('id', 5);
        $this->assertTrue($query1->execute());
        $this->assertTrue($query2->execute());
        self::$db->commit();

        $countquery = self::$db->select()->count('total')->from('user');
        $this->assertEquals($countquery->getColumn(), 3);

    }

    public function testQueryError()
    {
       $query = self::$db->table('user')->select('name');
       $query->where()
                ->beginAnd()
                    ->greaterEqual('id', 2)
                    ->greaterEqual('age', 18);

        $this->assertFalse($query->execute());
        $this->assertTrue($query->hasError());
        $this->assertInternalType("int", $query->errorCode());
        $this->assertInternalType("string", $query->errorMessage());
        $this->assertNotEquals('', $query->errorMessage());
    }
    
    public function testTransaction()
    {
        self::$db->beginTransaction();
        $this->assertTrue(self::$db->delete('user')->whereEqual('id', 6)->execute());
        $this->assertTrue(self::$db->delete('user')->whereEqual('id', 5)->execute());
        self::$db->commit();
    }

    public function testCountAfterTransaction()
    {
        $countquery = self::$db->select()->count('total')->from('user');
        $this->assertEquals(2, $countquery->getColumn());
    }

    public function testTransactionWithException()
    {
        try {
            self::$db->beginTransaction();
            $this->assertTrue(self::$db->delete('user')->whereEqual('id', 7)->execute());
            $this->assertTrue(self::$db->delete('user')->whereEqual('id', 8)->execute());
            throw new Exception('boo');
        }
        catch(Exception $e) {
            self::$db->rollback();
        }
        $countquery = self::$db->select()->count('total')->from('user');
        $this->assertEquals(2, $countquery->getColumn());
    }

    public function testTransactionWithSqlException()
    {
        try {
            self::$db->beginTransaction();
            $this->assertTrue(self::$db->delete('user')->whereEqual('id', 7)->execute());
            self::$db->delete('userxxx')->whereEqual('id', 8)->execute();
        }
        catch(Exception $e) {
            self::$db->rollback();
        }
        $countquery = self::$db->select()->count('total')->from('user');
        $this->assertEquals(2, $countquery->getColumn());
    }


    public function testDisableFk()
    {

        // not supported in pgsql
        if(self::$db->getDriver()->getDriverName() != 'pgsql'){
            self::$db->disableForeignKeys();
            $this->assertTrue(self::$db->delete('user_role')->whereEqual('role_id', 1)->execute());
            $this->assertTrue(self::$db->delete('user')->whereEqual('id', 7)->execute());
        
        } else {
          //  $this->assertFalse(self::$db->disableForeignKeys());
        }
    }

    public function testEnableFk()
    {
        self::$db->enableForeignKeys();
        $delete = self::$db->delete('user_role')->whereEqual('role_id', 2);
        $this->assertFalse($delete->execute());
        $this->assertTrue($delete->hasError());
    }

    public function testDropfk()
    {
        // not supported in sqlite / 
        if (self::$db->getDriver()->getDriverName() === 'sqlite'){
            $this->assertFalse(self::$db->dropForeignKey('fk_user_userrrole', 'user'));
        
        } else {
            $dropFk = self::$db->dropForeignKey('fk_user_userrrole', 'user');
            $this->assertTrue($dropFk);
            $delete = self::$db->delete('user_role')->whereEqual('role_id', 2);
            $this->assertTrue($delete->execute());

            // recreate failed
            $dropFk = self::$db->dropForeignKey('fk_user_userrrole', 'user');
            $this->assertFalse($dropFk);
        }
    }

    /**
     * @depends testDropfk
     */
    public function testCreatefk()
    {
        // not supported in sqlite / 
        if(self::$db->getDriver()->getDriverName() != 'sqlite'){
            // raise error
            $this->assertFalse(self::$db->addForeignKey('fk_XXX', 'user', 'role', 'user_role', 'role_id'));
            // pass if recreate ref
            $this->assertTrue( self::$db->table('user_role')->insert(array('role_id'=>2,'role_name'=> 'Standard'))->execute());
            $this->assertTrue( self::$db->addForeignKey('fk_XXX', 'user', 'role', 'user_role', 'role_id'));
            //error role 8
            $this->assertFalse( self::$db->table('user')->insert(array('id'=>9,'name'=> 'Bryan', 'age'=> 27, 'role' => '8'))->execute());
            //error fk
            $delete = self::$db->delete('user_role')->whereEqual('role_id', 2);
            $this->assertFalse($delete->execute());
            $this->assertTrue($delete->hasError());
        }
    }
    public function testHaving()
    {
        $query = self::$db->table('testhaving')->create()
            ->column('id', 'int', 'pk')
            ->column('name', 'varchar(50)', 'NULL')
            ->column('age', 'int', 'NULL')
            ->execute(); 

        self::$db->table('testhaving')->insert(array('id'=>1,'name'=> 'Bryan', 'age'=> 34))->execute();
        self::$db->table('testhaving')->insert(array('id'=>2,'name'=> 'Steve', 'age'=> 32))->execute();
        self::$db->table('testhaving')->insert(array('id'=>3,'name'=> 'John',  'age'=> 18))->execute();
        self::$db->table('testhaving')->insert(array('id'=>4,'name'=> 'Chris', 'age'=> 38))->execute();
        self::$db->table('testhaving')->insert(array('id'=>5,'name'=> 'Jane',  'age'=> 16))->execute();
        self::$db->table('testhaving')->insert(array('id'=>6,'name'=> 'Bryan', 'age'=> 28))->execute();
                   
        $this->assertEquals('[{"name":"Bryan","sumAge":62},{"name":"Chris","sumAge":38},{"name":"Steve","sumAge":32}]',
            self::$db->table('testhaving')->select()
                       ->column('name')->sum('age', 'sumAge')
                       ->having()->sum('age','>', 30)
                       ->groupBy('name')
                       ->getAll('json'));

        $this->assertEquals('[{"name":"Jane","sumAge":16},{"name":"John","sumAge":18}]',
            self::$db->table('testhaving')->select()
                       ->column('name')->sum('age', 'sumAge')
                       ->having()->fn('SUM','age','<=', 30)
                       ->groupBy('name')
                       ->getAll('json'));

        $this->assertEquals('[{"name":"Jane","sumAge":16},{"name":"John","sumAge":18}]',
            self::$db->table('testhaving')->select()
                       ->column('name')
                       ->sum('age', 'sumAge')
                       ->having()->sum('age','<=', 30)
                       ->groupBy('name')
                       ->getAll('json'));
       
        $query = self::$db->table('testhaving')->select()
                       ->column('name')
                       ->sum('age', 'sumAge')
                       ->having()
                            ->beginOr()
                            ->sum('age','<=', 30)
                            ->sum('age','>=', 38)
                            ->closeOr()
                       ->groupBy('name')
                       ->orderAsc('name');

        //$this->assertEquals('DEBUG', $query->sql());
        $this->assertEquals('[{"name":"Bryan","sumAge":62},{"name":"Chris","sumAge":38},{"name":"Jane","sumAge":16},{"name":"John","sumAge":18}]', $query->getAll('json'));

        // group by / having count 
        $query= self::$db->table('user')->select()
            ->column('name')
            ->groupBy('name')
            ->having()->count('>' , 0);

        $this->assertEquals('[{"name":"Bryan"}]', $query->getAll('json'));
    }

    //not testable
   // public function testDescructor()
  //  {
  //      self::$db = null;
  //  }


}
