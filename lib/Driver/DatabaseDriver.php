<?php declare(strict_types=1);

/** 
 *  ___      _        _
 * | _ \__ _| |_ __ _| |__  __ _ ___ ___
 * |  _/ _` |  _/ _` | '_ \/ _` (_-</ -_)
 * |_| \__,_|\__\__,_|_.__/\__,_/__/\___|
 * 
 * This file is part of Kristuff\Patabase.
 * (c) Kristuff <kristuff@kristuff.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version    1.0.0
 * @copyright  2017-2021 Kristuff
 */

namespace Kristuff\Patabase\Driver;

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
     * @var \PDO
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
     * Options for CREATE TABLE 
     *
     * @access protected
     * @var string
     */
    public function sqlCreateTableOptions(): string
    {
        return '';    
    } 

    /**
     * Gets/returns the default output format 
     *
     * @access public
     * @return string
     */
    public function defaultOutputFormat(): string
    {
        return $this->defaultOutputFormat;
    }

    /**
     * Get the current hostname
     *
     * @access public
     * @return string
     */
    public function getHostName(): string
    {
        return $this->hostname;    
    }

    /**
     * Get the current driver name
     *
     * @access public
     * @return string
     */
    public function getDriverName(): string
    {
        return $this->driverName;    
    }

    /**
     * Escape a given string with driver escape chars
     * 
     * @access public
     * @param string   $str  The value to escape
     *
     * @return string
     */
    public function escape(string $str): string
    {
       $list = explode('.', $str);
       return implode('.', $this->escapeList($list));
    }

    /**
     * Escape an array of string with driver escape chars
     *
     * @access public
     * @param array    $values  The array of values
     *
     * @return array
     */
    public function escapeList(array $values): array
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
     * @param array    $settings               The connection settings
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
    public function hasError(): bool
    {
        return !empty($this->error);
    }

    /**
     * Reset error
     *
     * @access public
     * @return void
     */
    public function cleanError(): void
    {
        $this->error = array();
    }

    /**
     * Error Code
     *
     * @access public
     * @return int
     */
    public function errorCode(): int
    {
        return !empty($this->error) ? $this->error['code']: '';
    }

    /**
     * Error Message
     *
     * @access public
     * @return bool     True if the query has genaretd an error
     */
    public function errorMessage(): string
    {
        return !empty($this->error) ? $this->error['message'] : '';
    }

    /**
     * Get the PDO connection
     *
     * @access public
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }
    
    /**
     * Prepare and execute a query 
     *
     * @access public
     * @param string   $sql            The SQL query
     * @param array    $parameters     The SQL parameters
     *
     * @return bool     true if the query is executed with success, otherwise false
     */
    public function prepareAndExecuteSql(string $sql, array $parameters = []): bool
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
    public function closeConnection(): void
    {
        $this->pdo = null;
    }

    /**
     * Create a PDO connection from given settings
     *
     * @access public
     * @param array    $settings
     *
     * @return void
     */
    abstract protected function createConnection(array $settings): void;

    /**
     * Escape identifier
     *
     * @access public
     * @param string   $identifier
     *
     * @return string
     */
    abstract public function escapeIdentifier(string $identifier): string;

    /**
     * Escape value
     *
     * @access public
     * @param string   $value
     *
     * @return string
     */
    abstract public function escapeValue(string $value) : string;
   
    /**
     * Get last inserted id
     *
     * @access public
     * @return string
     */
    abstract public function lastInsertedId(): string;

    /**
     * Enable foreign keys
     *
     * @access public
     * @return void
     */
    abstract public function enableForeignKeys(): void;

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    abstract public function disableForeignKeys(): void;

    /**
     * Get whether foreign keys are enabled or not
     * 
     * @access public
     * @return bool     true if foreign keys are enabled, otherwise false
     */
    abstract function isForeignKeyEnabled() : bool;

    /**
     * Add a foreign key
     * 
     * @access public
     * @param string   $fkName         The constraint name
     * @param string   $srcTable       The source table
     * @param string   $srcColumn      The source column 
     * @param string   $refTable       The referenced table
     * @param string   $refColumn      The referenced column
     *
     * @return bool    True if the foreign key has been created, otherwise false
     */
    abstract public function addForeignKey(string $fkName, string $srcTable, string $srcColumn, string $refTable, string $refColumn) : bool;

    /**
     * Drop a foreign key
     * 
     * @access public
     * @param string   $fkName         The constraint name
     * @param string   $tableName      The source table
     *
     * @return bool    True if the foreign key has been dropped, otherwise false
     */
    abstract public function dropForeignKey(string $fkName, string $tableName): bool;

    /**
     * Get the SQL for show tables
     *
     * @access public
     * @return string
     */
    abstract public function sqlShowTables(): string;

    /**
     * Get the SQL for RANDOM function
     *
     * @access public
     * @param  mixed   $seed   The seed for random function
     *
     * @return string
     */
    abstract public function sqlRandom(): string;

    /**
     * Get the SQL for auto increment column
     *
     * @access public
     * @param string   $type   The sql column type
     *
     * @return string
     */
    abstract public function sqlColumnAutoIncrement(string $type): string;
}