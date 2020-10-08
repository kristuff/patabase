<?php

/*
 *   ____         _          _
 *  |  _ \  __ _ | |_  __ _ | |__    __ _  ___   ___
 *  | |_) |/ _` || __|/ _` || '_ \  / _` |/ __| / _ \
 *  |  __/| (_| || |_| (_| || |_) || (_| |\__ \|  __/
 *  |_|    \__,_| \__|\__,_||_.__/  \__,_||___/ \___|
 *  
 * This file is part of Kristuff\Patabase.
 *
 * (c) Kristuff <contact@kristuff.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version    0.4.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Driver\Mysql;

use Kristuff\Patabase\Driver\ServerDriver;

/**
 * Class MssqlDriver
 *
 * Microsoft SQL Server Driver
 *
 */
class MssqlDriver extends ServerDriver
{

    /**
     * List of DSN attributes
     *
     * @access protected
     * @var array
     */
    protected $dsnAttributes = array(
        'hostname',
        'username',
        'password',
        'database'
    );

    /**
     * Escape identifier
     *
     * @access public
     * @param  string  $identifier
     *
     * @return string
     */
    public function escapeIdentifier($identifier)
    {
        return '['.$identifier.']';
    }

    /**
     * Escape value
     *
     * @access public
     * @param  string  $value
     *
     * @return string
     */
    public function escapeValue($value)
    {
        return "'".$value."'";
    }

    /**
     * Create a new PDO connection
     *
     * @access public
     * @param  array   $settings
     * @return void
     */
    public function createConnection(array $settings)
    {
        $port    = !empty($settings['port'])     ?  ','.$settings['port']        : '';
        $dbname  = !empty($settings['database'])   ?  ';Database='.$settings['database']    : '';

        $this->pdo = new \PDO(
            'sqlsrv:Server='.$settings['hostname'] .$port .$dbname,
            $settings['username'],
            $settings['password'],
            array()
        );

        // emulate prepare seems to be false by default, but in case that change 
        // @see https://docs.microsoft.com/en-us/sql/connect/php/pdo-prepare?view=sql-server-ver15
        //$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    }

    /**
     * Get last inserted id
     *
     * @access public
     * @return integer
     */
    public function lastInsertedId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Enable foreign keys
     *
     * @access public
     * @return void
     */
    public function enableForeignKeys()
    {
        $this->pdo->exec('EXEC sp_MSforeachtable @command1="ALTER TABLE ? CHECK CONSTRAINT ALL"; GO;');
    }

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    public function disableForeignKeys()
    {
        $this->pdo->exec('EXEC sp_MSforeachtable @command1="ALTER TABLE ? NOCHECK CONSTRAINT ALL"; GO;');
    }

    /**
     * Add a foreign key
     * 
     * @access public
     * @param  string  $fkName      The constraint name
     * @param  string  $srcTable    The source table
     * @param  string  $srcColumn   The source column 
     * @param  string  $refTable    The referenced table
     * @param  string  $refColumn   The referenced column
     * @return bool                 Query success
     */
    public function addForeignKey($fkName, $srcTable, $srcColumn, $refTable, $refColumn)
    {
        $sql = sprintf('ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s)',
                       $this->escape($srcTable),
                       $fkName,
                       $this->escape($srcColumn),
                       $this->escape($refTable),
                       $this->escape($refColumn)
        );
        $query = $this->pdo->prepare($sql);
        return $query->execute();
    }

    /**
     * Drop a foreign key
     * 
     * @access public
     * @param  string  $fkName      The constraint name
     * @param  string  $tableName   The source table
     * @return bool                 Query success
     */
    public function dropForeignKey($fkName, $tableName)
    {
        $sql = sprintf('ALTER TABLE %s DROP CONSTRAINT (%s)',
                       $this->escape($tableName),
                       $fkName
        );
        $query = $this->pdo->prepare($sql);
        return $query->execute();
    }

    /**
     * Checks if a database exists
     *
     * @access public
     * @param  string $databaseName     The database name
     * @return bool                     True if the database exists, otherwise false.
     */
    public function databaseExists($databaseName)
    {
        // https://msdn.microsoft.com/en-us/library/ms186274.aspx
        $sql = trim(sprintf("SELECT DB_ID(N'%s') AS [DatabaseID]", $this->driver->escape($databaseName)));
        $query = $this->driver->getConnection()->prepare($sql);
        $query->execute();
        return $query->rowCount() > 0;
    }

    /**
     * Create a database
     *
     * @access public
     * @param  string   $databaseName   The database name
     * @param  string   $owner          The database owner. This parameter is not honored in Mssql.
     *
     * @return bool     True if the database has been created, otherwise false.
     */
    public function createDatabase($databaseName, $owner)
    {
        $sql = trim(sprintf('CREATE DATABASE %s',  $this->escape($databaseName)));
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Grant user permissions on given database
     *
     * @access public
     * @param  string $databaseName     The database name
     * @param  string $userName         The user name
     * @return bool                     Query success
     */
    public function grantUser($databaseName, $userName)
    {
        //TODO
    }

    /**
     * Create a user
     *
     * @access public
     * @param  string $userName         The user name
     * @param  string $userpassword     The user password
     * @return bool                     Query success
     */
    public function createUser($userName, $userPassword)
    {
        //TODO
    }

    /**
     * Drop a user
     *
     * @access public
     * @param  string   $userName               The user name
     * @param  bool     $ifExists (optional)    Set whether the user must be deleted only when exists. Default is False.
     * @return bool     True if the user has been dropped or does not exist and $ifExists 
     *                  is set to True, otherwise False. 
     */
    public function dropUser($userName, $ifExists = FALSE)
    {
        //TODO
    }

    /**
     * Get the SQL for show databases
     *
     * @access public
     * @return string
     */
    public function sqlShowDatabases()
    {
        //TODO    
    }
   
    /**
     * Get the SQL for show tables
     *
     * @access public
     * @return string
     */
    public function sqlShowTables()
    {
        //TODO
    }

    /**
     * Get the SQL for show users
     *
     * @access public
     * @return string
     */
    public function sqlShowUsers()
    {
        //TODO
    }

    /**
     * Get the SQL for random function 
     *
     * @access public
     * @param  int      $seed    The random seed. Default is null.
     *
     * @return string         
     */
    public function sqlRandom($seed = NULL)
    {
        return sprintf('RAND(%s)',  !empty($seed) ? $seed : '');   
    }
        
    /**
     * Get the SQL for auto increment column
     *
     * @access public
     * @param  string   $type   The sql column type 
     * @return string
     */
    public function sqlColumnAutoIncrement($type)
    {
        return $type .' IDENTITY(1,1)';
    }
}