<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Kristuff\Patabase\Server;
use PHPUnit\Framework\TestCase;

abstract class ServerTest extends TestCase
{

    /**
     * @var Kristuff\Patabase\Server
     */
    protected static $srv;

    public function testDriverName()
    {
       $name= self::$srv->getDriverName();
       $this->assertTrue(in_array($name, ['sqlite', 'mysql', 'pgsql']));
    }

    /**
     * @expectedException Kristuff\Patabase\Exception\MissingArgException
     */
    public function testMissingRequiredParameter()
    {
        new Server(array());
    }

    public function testDatabaseCreate()
    {
        $this->assertTrue(self::$srv->createDatabase('patabase'));
        $this->assertFalse(self::$srv->createDatabase('patabase'));
    }

    public function testDatabaseExists()
    {
        $this->assertFalse(self::$srv->databaseExists('notexistingdb'));
        $this->assertTrue(self::$srv->databaseExists('patabase'));
    }

    public function testGetDatabases()
    {
        $dbs = self::$srv->getDatabases();
        $this->assertInternalType('array', $dbs);
        $this->assertTrue(in_array('patabase', $dbs));
    }

    /**
     * @depends testDatabaseExists
     */
    public function testCreateUser()
    {
        $this->assertTrue(self::$srv->createUser('toto','pass'));
        $this->assertFalse(self::$srv->createUser('toto','pass'));
    }

    /**
     * @depends testCreateUser
     */
    public function testGrantUser()
    {
        $this->assertTrue(self::$srv->grantUser('patabase','toto'));
        // $this->assertFalse(self::$srv->grantUser('XXXpatabase','toto'));
    }
    
    /**
     * @depends testCreateUser
     */
    public function testGetusers()
    {
        $usrs = self::$srv->getUsers();
        $this->assertInternalType('array', $usrs);
        $this->assertTrue(in_array('toto', $usrs));
    }
    
    /**
     * @depends testCreateUser
     */
    public function testUserExists()
    {
        $this->assertTrue(self::$srv->userExists('toto'));
    }

    /**
     * @depends testGrantUser
     */
     public function testDropDatabase()
    {
        $this->assertTrue(self::$srv->dropDatabase('patabase'));
        $this->assertTrue(self::$srv->dropDatabase('notexistingdb', true));
        $this->assertFalse(self::$srv->dropDatabase('notexistingdb'));
    }

     /**
     * @depends testDropDatabase
     */
    public function testDropUser()
    {
        $this->assertTrue(self::$srv->dropUser('toto'));
        $this->assertFalse(self::$srv->dropUser('totoXXXX'));
    }

    public function testError()
    {
        $failedDropUser = self::$srv->dropUser('XOXO');
        $this->assertFalse($failedDropUser);
        $this->assertTrue(self::$srv->hasError());
        $this->assertInternalType("int", self::$srv->errorCode());
        $this->assertInternalType("string", self::$srv->errorMessage());
        $this->assertNotEquals('', self::$srv->errorMessage());
    }

    public function testCreateDatabaseWithUser()
    {
        $created = self::$srv->createDatabaseAndUser('patabaseTest','tototo', 'passss');
        $this->assertTrue($created);

        // debug
        //if (! $created) {
            $this->assertEquals(' hello world', self::$srv->getDatabases());
        //}

    }

    public function testDescructor()
    {
       self::$srv= null;
    }
}