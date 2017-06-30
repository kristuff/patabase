<?php

/*
 * This file is part of Kristuff\Patabase.
 *
 * (c) Kristuff <contact@kristuff.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version    0.1.0
 * @copyright  2017 Kristuff
 */

namespace Kristuff\Patabase\Driver\Sqlite;

use Kristuff\Patabase\Driver\DatabaseDriver;

/**
 * Class SqliteDriver
 *
 * SQLite 3 Driver
 *
 * Data types
 *  INT, INTEGER, TINYINT, SMALLINT, MEDIUMINT, BIGINT, UNSIGNED BIG INT, INT2, INT8 	=> INTEGER 
 *  CHARACTER(20), VARCHAR(255), VARYING CHARACTER(255), NCHAR(55), 
 *      NATIVE CHARACTER(70), NVARCHAR(100), CLOB, TEXT                                 => TEXT
 *  BLOB, no datatype specified 	                                                    => BLOB
 *  REAL, DOUBLE, DOUBLE PRECISION, FLOAT 	                                            => REAL
 *  NUMERIC, DECIMAL(10,5), BOOLEAN, DATE, DATETIME 	                                => NUMERIC
 */
class SqliteDriver extends DatabaseDriver
{

    /**
     * List of DSN attributes
     * 
     * In Sqlite, the database attribute represent the full path to the database
     * To create a database in memory, define the database to :memory: 
     *
     * @access protected
     * @var array
     */
    protected $dsnAttributes = array(
        'database'
    );

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
        $this->pdo = new \PDO('sqlite:'.$settings['database']); 

        // https://www.sqlite.org/foreignkeys.html
        // Foreign key constraints are disabled by default (for backwards compatibility), 
        // so must be enabled separately for each database connection.
        $this->enableForeignKeys();
    }

    /**
     * Escape an identifier
     *
     * @access public
     * @param  string  $identifier
     *
     * @return string
     */
    public function escapeIdentifier($identifier)
    {
        return '"'.$identifier.'"';
    }
       
    /**
     * Escape a value
     *
     * @access public
     * @param  string  $value
     *
     * @return string
     */
    public function escapeValue($value)
    {
        return '"'.$value.'"';
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
     * Get whether foreign keys are enabled or not
     *
     * https://www.sqlite.org/foreignkeys.html
     *  Foreign key constraints are disabled by default (for backwards compatibility), so must be enabled separately for 
     *  each database connection. (Note, however, that future releases of SQLite might change so that foreign key constraints 
     *  enabled by default. Careful developers will not make any assumptions about whether or not foreign keys are enabled by 
     *  default but will instead enable or disable them as necessary.) The application can also use a PRAGMA foreign_keys 
     *  statement to determine if foreign keys are currently enabled. 
     * @access public
     * @return bool True if foreign keys are enabled, otherwise false
     */
    public function isForeignKeyEnabled()
    {
        $query = $this->pdo->prepare('PRAGMA foreign_keys');
        return  $query->execute() && (int) $query->fetchColumn() === 1;
    }

    /**
     * Enable foreign keys
     *
     * @access public
     * @return void
     */
    public function enableForeignKeys()
    {
        $this->pdo->exec('PRAGMA foreign_keys = ON');
    }

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    public function disableForeignKeys()
    {
        $this->pdo->exec('PRAGMA foreign_keys = OFF');
    }

    /**
     * Add a foreign key
     *
     * This is not supported on sqlite and returns false.
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
        return false;
    }

    /**
     * Drop a foreign key
     *
     * This is not supported on sqlite and returns false.
     * 
     * @access public
     * @param  string  $fkName          The constraint name
     * @param  string  $tableName       The source table
     *
     * @return bool    True if the foreign key has been dropped, otherwise false
     */
    public function dropForeignKey($fkName, $tableName)
    {
        return false;
    }
    
    /**
     * Get the SQL for show tables
     *
     * @access public
     * @return string
     */
    public function sqlShowTables()
    {
        return 'SELECT name FROM sqlite_master WHERE type = "table";';
    }

    /**
     * Get the SQL for random function 
     *
     * @access public
     * @param  mixed    $seed    Random seed. Default is null.
     *
     * @return string         
     */
    public function sqlRandom($seed = null)
    {
        $seed = !empty($seed) ? $seed : '';  
        return sprintf('random(%s)', $seed);   
    }

    /**
     * Get the SQL for auto increment column
     *
     * @access public
     * @param  string   $type   The sql column type
     * 
     * @return string
     */
    public function sqlColumnAutoIncrement($type)
    {
        // http://www.sqlite.org/datatypes.html
        //  One exception to the typelessness of SQLite is a column whose type is INTEGER PRIMARY KEY. (And you 
        //  must use "INTEGER" not "INT". A column of type INT PRIMARY KEY is typeless just like any other.) 
        //  INTEGER PRIMARY KEY columns must contain a 32-bit signed integer. Any attempt to insert non-integer data 
        //  will result in an error. INTEGER PRIMARY KEY columns can be used to implement the equivalent of AUTOINCREMENT.
        // 
        // https://sqlite.org/autoinc.html
        //  If the AUTOINCREMENT keyword appears after INTEGER PRIMARY KEY, that changes the automatic ROWID assignment 
        //  algorithm to prevent the reuse of ROWIDs over the lifetime of the database. In other words, the purpose 
        //  of AUTOINCREMENT is to prevent the reuse of ROWIDs from previously deleted rows. 
        return 'INTEGER';
    }
}