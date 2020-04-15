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
 * @version    0.2.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase\Query;
use Kristuff\Patabase\Query\QueryBase;
use Kristuff\Patabase\Exception;
use Kristuff\Patabase\SqlException;
use Kristuff\Patabase\Driver;

/**
 * Class QueryBuilder
 *
 * Abstract base class for parametized sql queries
 */
abstract class QueryBuilder extends QueryBase
{

    /**
     * Sorting direction
     *
     * @access public
     * @var string
     */
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';
    
    /**
     * PDO Statement object
     *
     * @access protected
     * @var    PDO                  $pdoStatement
     */
    protected $pdoStatement = null;

    /**
     * List of parameters passed to PDO statement
     *
     * @access public
     * @var    array                $pdoParameters
     */
    protected $pdoParameters = array();

    /**
     * List of parameters passed to query
     *
     * @access public
     * @var    array                $parameters
     */
    protected $parameters = array();

    /**
     * Where conditions object
     *
     * @access protected
     * @var    Query\Where          $where
     */
    protected $where = null;
    
    /**
     * Having conditions object
     *
     * @access protected
     * @var    Query\Having         $having
     */ 
    protected $having = null;

    /**
     * The Driver instance
     *
     * @access protected
     * @var    Driver\DatabaseDriver    $driver 
     */
    protected $driver = null;

    /**
     * SQL query string  
     *
     * @access public
     * @return string
     */
    abstract function sql();

    /**
     * Escape a given string with driver escape chars
     * 
     * @access public
     * @param  string   $str        The value to escape
     *
     * @return string
     */
    public function escape($str)
    {
       return $this->driver->escape($str);
    }

    /**
     * Escape an array of string with driver escape chars
     *
     * @access public
     * @param  array    $values     The array of values
     *
     * @return array
     */
    public function escapeList(array $values)
    {
        $newList = array();
        foreach ($values as $identifier) {
            $newList[] = $this->escape($identifier);
        }
        return $newList;
    }

    /**
     * Constructor, init the query by define the driver
     *
     * @access public
     * @param  Driver\DatabaseDriver    $driver         The driver instance
     *
     * @return string
     */
    public function __construct( $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct()
    {
        $this->driver = null;
    }

    /**
     * Define a key/value parameter
     *
     * @access public
     * @param  string       $columName          The name
     * @param  mixed        $value              The value
     * @return void
     */
    public function setSqlParameter($name, $value)
    {
        $this->pdoParameters[$name] = $value;    
    }

    /**
     * Get whether a parameter with given name exists
     *
     * @access protected
     * @param  string       $name               the column name
     *
     * @return bool
     */
    public function sqlParameterExists($name)
    {
        return array_key_exists($name, $this->pdoParameters);
    }

    /**
     * Prepare the SQL query
     *
     * @access public
     * @return void
     */
    public function prepare()
    {
         try {
            // prepare is in a try catch block because sqlite for example could raise 
            // an exception when prepareing the statement (with invalid table name for example)
            // when mysql and postres wont. 
            $this->pdoStatement = $this->driver->getConnection()->prepare($this->sql());
            return TRUE;

        } catch (\PDOException $e) {

            // transactions must be in try catch block
            if ($this->driver->getConnection()->inTransaction()) {
                throw new SqlException($e->getMessage(), (int) $e->getCode());
            }

            // register error 
            $this->error['code'] = (int)$e->getCode();
            $this->error['message'] = $e->getMessage();
            return false;
        }
    }   

    /**
     * Bind values
     *
     * @access protected
     * @return void
     */
    protected function bindValues()
    {
        // pdo statement may be not set at this stage if prepare failed
        if (isset($this->pdoStatement)) {

            // bind query parameters
            foreach ($this->pdoParameters as $key => $val) {

                // define param type TODO LOB
                $paramType = \PDO::PARAM_STR; // default

                if (!isset($val)) {
                    $paramType =  \PDO::PARAM_NULL;
                
                } elseif (is_int($val)) {
                    $paramType =  \PDO::PARAM_INT;
                
                } elseif (is_bool($val)) {
                    $paramType =  \PDO::PARAM_BOOL;
                } 
                
                // bind value
                $this->pdoStatement->bindValue($key, $val, $paramType);
            }
        }
    }   

    /**
     * Execute the query
     *
     * @access public
     * @return bool     true if the query is executed with success, otherwise false
     */
    public function execute()
    {
        try {
            // prepare bind execute
            if (!isset($this->pdoStatement)){
               if (!$this->prepare()){
                   return false;
               }
            }
            $this->bindValues();
            return $this->pdoStatement ? $this->pdoStatement->execute() : false;

        } catch (\PDOException $e) {

            // transactions must be in try catch block
            if ($this->driver->getConnection()->inTransaction()) {
                throw new SqlException($e->getMessage(), (int) $e->getCode());
            }

            // register error 
            $this->error['code'] = (int)$e->getCode();
            $this->error['message'] = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the number of rows affected by the last SQL INSERT, UPDATE or DELETE
     *
     * @access public
     * @return int     This function returns -1 if there is no executed query
     */
    public function rowCount()
    {
        return (empty(!$this->pdoStatement)) ? $this->pdoStatement->rowCount() : -1;
    }
    
    /**
     * Returns the sql query output in given format
     *
     *  TODO: format constants 
     *
     * @access public
     * @param  QueryBuilder     $query              The QueryBuilder instance
     * @param  string           $outputFormat       The output format
     * 
     * @return mixed                     
     */
    protected static function fetchOutput(QueryBuilder $query, $executed, $ouputFormat)
    {
        switch ($ouputFormat){
            case 'asso':    
                return $executed ? $query->pdoStatement->fetchAll(\PDO::FETCH_ASSOC) :  array();

            case 'obj':     
                return $executed ? $query->pdoStatement->fetchAll(\PDO::FETCH_OBJ) :    NULL;

            case 'cols':     
                return $executed ? $query->pdoStatement->fetchAll(\PDO::FETCH_COLUMN) :  array();

            case 'json':    
                $results = $executed ? $query->pdoStatement->fetchAll(\PDO::FETCH_ASSOC) :  array();
                return json_encode($results, JSON_NUMERIC_CHECK);   

            case 'jsonpp':    
                $results = $executed ? $query->pdoStatement->fetchAll(\PDO::FETCH_ASSOC) :  array();
                return json_encode($results, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);   
                
            default:
                throw new Exception\InvalidArgException('The specified output format is invalid.');
        }
    }
 
}