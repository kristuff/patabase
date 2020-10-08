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
 *
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Driver;

use Kristuff\Patabase;
use Kristuff\Patabase\Driver;
use Kristuff\Patabase\Exception;
use Kristuff\Patabase\Output;

/**
 *  Class DatabaseDriver
 *
 *  Base class for Database(only) drivers
 */
abstract class DatabaseDriver
{
    /**
     * PDO connection
     *
     * @access protected
     * @var PDO
     */
    protected $pdo = null;

    /**
     * List of DSN attributes
     * The Data Source Name, or DSN, contains the information required 
     * to connect to the database. 
     *
     * @access protected
     * @var array
     */
    protected $dsnAttributes = array();

    /**
     * Error
     *
     * @access protected
     * @var    array
     */
    protected $error = array();

    /**
     * The default output format
     *
     * @access private
     * @var    string
     */
    private $defaultOutputFormat = Output::ASSOC;

    /**
     * Options for CREATE TABLE 
     *
     * @access protected
     * @var string
     */
    public function sqlCreateTableOptions()
    {
        return '';    
    } 

    /**
     * Gets/returns the default output format 
     *
     * @access public
     * @return string
     */
    public function defaultOutputFormat()
    {
        return $this->defaultOutputFormat;
    }

    /**
     * the current hostname
     *
     * @Var string 
     */
    private $hostname;

    /**
     * the current driver
     *
     * @Var string 
     */
    private $driverName;

    /**
     * Get the current hostname
     *
     * @access public
     * @return string
     */
    public function getHostName()
    {
        return $this->hostname;    
    }

    /**
     * Get the current driver name
     *
     * @access public
     * @return string
     */
    public function getDriverName()
    {
        return $this->driverName;    
    }

    /**
     * Escape a given string with driver escape chars
     * 
     * @access public
     * @param  string   $str  The value to escape
     *
     * @return string
     */
    public function escape($str)
    {
       $list = explode('.', $str);
       return implode('.', $this->escapeList($list));
    }

    /**
     * Escape an array of string with driver escape chars
     *
     * @access public
     * @param  array    $values  The array of values
     *
     * @return array
     */
    public function escapeList(array $values)
    {
        $newList = array();
        foreach ($values as $identifier) {
            $newList[] = $this->escapeIdentifier($identifier);
        }
        return $newList;
    }

    /**
     * Constructor
     *
     * @access public
     * @param  array    $settings               The connection settings
     */
    public function __construct(array $settings)
    {
        // check for required attributes
        foreach ($this->dsnAttributes as $attribute) {
            if (! array_key_exists($attribute, $settings)) {
                throw new Exception\MissingArgException('This configuration parameter is missing: "'.$attribute.'"');
            }
        }

        // defaut output format
        if (array_key_exists('default_output_format', $settings)){
            $format = $settings['default_output_format'];
            if (!in_array($format, $this->outputFormats)){
                throw new Exception\InvalidArgException('The default output format specified is invalid.');
            } 
            $this->defaultOutputFormat = $format;
        }

        $this->createConnection($settings);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->hostname = array_key_exists('hostname',$settings) && $settings['hostname'] ? $settings['hostname'] : '';
        $this->driverName = $settings['driver'];
    }

    /**
     * Has error
     *
     * @access public
     * @return bool     True if the query has genaretd an error
     */
    public function hasError()
    {
        return !empty($this->error);
    }

    /**
     * Reset error
     *
     * @access public
     * @return void
     */
    public function cleanError()
    {
        $this->error = array();
    }

    /**
     * Errors
     *
     * @access public
     * @return bool     True if the query has genaretd an error
     */
    public function errorCode()
    {
        return !empty($this->error) ? $this->error['code']: '';
    }

    /**
     * Errors
     *
     * @access public
     * @return bool     True if the query has genaretd an error
     */
    public function errorMessage()
    {
        return !empty($this->error) ? $this->error['message'] : '';
    }

    /**
     * Get the PDO connection
     *
     * @access public
     * @return PDO
     */
    public function getConnection()
    {
        return $this->pdo;
    }
    
    /**
     * Prepare and execute a query 
     *
     * @access public
     * @param  string   $sql            The SQL query
     * @param  array    $parameters     The SQL parameters
     *
     * @return bool     true if the query is executed with success, otherwise false
     */
    public function prepareAndExecuteSql($sql, array $parameters = [])
    {
        // clear the current errors
        $this->cleanError();
        try {
            // prepare and execute
            $pdoStatement = $this->pdo->prepare($sql);
            return $pdoStatement->execute($parameters);
        } catch (\PDOException $e) {
            // register error
            $this->error['code'] = (int)$e->getCode();
            $this->error['message'] = $e->getMessage();
            return false;
        }
    }    

    /**
     * Release the PDO connection
     *
     * @access public
     * @return void
     */
    public function closeConnection()
    {
        $this->pdo = null;
    }

    /**
     * Create a PDO connection from given settings
     *
     * @access public
     * @param  array    $settings
     *
     * @return void
     */
    abstract protected function createConnection(array $settings);

    /**
     * Escape identifier
     *
     * @access public
     * @param  string   $identifier
     *
     * @return string
     */
    abstract public function escapeIdentifier($identifier);

    /**
     * Escape value
     *
     * @access public
     * @param  string   $value
     *
     * @return string
     */
    abstract public function escapeValue($value);
   
    /**
     * Get last inserted id
     *
     * @access public
     * @return integer
     */
    abstract public function lastInsertedId();

    /**
     * Enable foreign keys
     *
     * @access public
     * @return void
     */
    abstract public function enableForeignKeys();

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    abstract public function disableForeignKeys();

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
    abstract public function addForeignKey($fkName, $srcTable, $srcColumn, $refTable, $refColumn);

    /**
     * Drop a foreign key
     * 
     * @access public
     * @param  string   $fkName         The constraint name
     * @param  string   $tableName      The source table
     *
     * @return bool    True if the foreign key has been dropped, otherwise false
     */
    abstract public function dropForeignKey($fkName, $tableName);

    /**
     * Get the SQL for show tables
     *
     * @access public
     * @return string
     */
    abstract public function sqlShowTables();

    /**
     * Get the SQL for RANDOM function
     *
     * @access public
     * @param  mixed   $seed   The seed for random function
     *
     * @return string
     */
    abstract public function sqlRandom();

    /**
     * Get the SQL for auto increment column
     *
     * @access public
     * @param  string   $type   The sql column type
     *
     * @return string
     */
    abstract public function sqlColumnAutoIncrement($type);
}