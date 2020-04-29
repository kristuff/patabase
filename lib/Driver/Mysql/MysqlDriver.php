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
* @version    0.3.0
 *
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Driver\Mysql;

use Kristuff\Patabase\Driver\ServerDriver;

/**
 * Class MysqlDriver
 *
 * Data types:
 *  CHAR, VARCHAR, BINARY, VARBINARY, BLOB, TEXT, ENUM, SET
 *  INTEGER, INT, SMALLINT, TINYINT, MEDIUMINT, BIGINT
 *  DECIMAL, NUMERIC
 *  FLOAT, DOUBLE
 *  BIT(n)
 *   
 */
class MysqlDriver extends ServerDriver
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
        return '`' . $identifier .'`';
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
     *
     * @return void
     */
    public function createConnection(array $settings)
    {
        $charset = !empty($settings['charset'])  ?  ';charset='.$settings['charset']  : ';charset=utf8';
        $port    = !empty($settings['port'])     ?  ';port='.$settings['port']        : '';
        $dbname  = !empty($settings['database']) ?  ';dbname='.$settings['database']  : '';
        $options = [
          //  \PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new \PDO(
            'mysql:host='.$settings['hostname'] .$port .$dbname .$charset,
            $settings['username'],
            $settings['password'],
            $options
        );

        // emulate prepare is true by default in mysql
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
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
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    public function disableForeignKeys()
    {
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    }
    
    /**
     * Add a foreign key
     * 
     * @access public
     * @param  string   $fkName         The constraint name
     * @param  string   $srcTable       The source table
     * @param  string   $srcColumn      The source column 
     * @param  string   $refTable       The referenced table
     * @param  string   $refColumn      The referenced column
     *
     * @return bool    True if the foreign key has been created, otherwise false
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
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Drop a foreign key
     * 
     * @access public
     * @param  string   $fkName         The constraint name
     * @param  string   $tableName      The source table
     *
     * @return bool    True if the foreign key has been dropped, otherwise false
     */
    public function dropForeignKey($fkName, $tableName)
    {
        $sql = sprintf('ALTER TABLE %s DROP FOREIGN KEY %s',
                       $this->escape($tableName),
                       $fkName
        );
        return $this->prepareAndExecuteSql($sql);
    }
    
    /**
     * Checks if a database exists
     *
     * @access public
     * @param  string   $databaseName   The database name
     *
     * @return bool     True if the given database exists, otherwise false.
     */
    public function databaseExists($databaseName)
    {
        $sql = 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbName'; 
        $query = $this->pdo->prepare($sql);
        $query->bindValue(':dbName',  $databaseName, \PDO::PARAM_STR);
        $query->execute();
        return (bool) $query->fetchColumn();
    }

    /**
     * Create a database
     *
     * @access public
     * @param  string   $databaseName   The database name
     * @param  string   $owner          (optional) The database owner. This parameter is not honored in Mysql.
     *
     * @return bool     True if the database has been created, otherwise false.
     */
    public function createDatabase($databaseName, $owner = null)
    {
        $sql = trim(sprintf('CREATE DATABASE %s',  $this->escape($databaseName)));
        return $this->prepareAndExecuteSql($sql);
    }
    
    /**
     * Create a user
     *
     * @access public
     * @param  string   $userName         The user name
     * @param  string   $userpassword     The user password
     *
     * @return bool     True if the user has been created, otherwise false. 
     */
    public function createUser($userName, $userPassword)
    {
        $sql = trim(sprintf('CREATE USER %s@%s IDENTIFIED BY %s', 
                    $this->escape($userName),
                    $this->escape($this->getHostName()),
                    "'" . $userPassword ."'"
        ));
        return $this->prepareAndExecuteSql($sql);
   }

    /**
     * Drop a user
     *
     * @access public
     * @param  string   $userName         The user name
     * @param  bool     $ifExists         (optional) True if the user must be deleted only when exists. Default is false.
     *
     * @return bool     True if the user has been dropped or does not exist when $ifExists is set to True, otherwise false. 
     */
    public function dropUser($userName, $ifExists = false)
    {
        $sql = trim(sprintf('DROP USER %s %s@%s', 
                    $ifExists === true ? 'IF EXISTS': '',
                    $this->escape($userName),
                    $this->escape($this->getHostName())
        ));
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Grant user permissions on given database
     *
     * @access public
     * @param  string   $databaseName     The database name
     * @param  string   $userName         The user name
     *
     * @return bool     True if the user has been granted, otherwise false. 
     */
     public function grantUser($databaseName, $userName)
    {
        $sql = trim(sprintf('GRANT ALL ON %s.* TO %s@%s; FLUSH PRIVILEGES;', 
            $this->escape($databaseName),
            $this->escape($userName),
            $this->escape($this->getHostName())
        ));
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Get the SQL for show databases
     *
     * @access public
     * @return string
     */
    public function sqlShowDatabases()
    {
        return 'SHOW DATABASES';
    }

    /**
     * Get the SQL for show tables
     *
     * @access public
     * @return string
     */
    public function sqlShowTables()
    {
        return 'SHOW TABLES';
    }

    /**
     * Get the SQL for show users
     *
     * @access public
     * @return string
     */
    public function sqlShowUsers()
    {
        return 'SELECT DISTINCT user FROM mysql.user';
    }

    /**
     * Get the options for CREATE TABLE query
     *
     * @access protected
     * @return string
     */
    public function sqlCreateTableOptions() 
    {
        $engine =  !empty($settings['engine'])  ? $settings['engine']  : 'InnoDB';
        $charset = !empty($settings['charset']) ? $settings['charset'] : 'utf8';
        $collate = !empty($settings['collate']) ? $settings['collate'] : 'utf8_unicode_ci';
        return sprintf('ENGINE=%s DEFAULT CHARSET=%s COLLATE=%s;', $engine, $charset, $collate);
    } 
    
    /**
     * Get the SQL for random function 
     *
     * @access public
     * @param  int      $seed    The random seed. Default is null.
     *
     * @return string         
     */
    public function sqlRandom($seed = null)
    {
        return sprintf('rand(%s)', !empty($seed) ? $seed : '');   
    }

    /**
     * Gets/returns the SQL for auto increment column.
     *
     * @access public
     * @param  string   $type   The sql column type
     * 
     * @return string
     */
    public function sqlColumnAutoIncrement($type)
    {
        return $type .' AUTO_INCREMENT';
    }

}
