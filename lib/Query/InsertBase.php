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

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase\Query\QueryBuilder;
use Kristuff\Patabase\Driver\DatabaseDriver;

/**
 * Class InsertBase
 *
 * Abstract base class for Insert or update
 */
abstract class InsertBase extends QueryBuilder
{

    /**
     * Table name
     *
     * @access private
     * @var    string
     */
    protected $tableName = null;

    /**
     * Constructor
     *
     * @access public
     * @param DatabaseDriver    $driver         The driver instance
     * @param string            $tableName      The table name
     */
    public function __construct(DatabaseDriver $driver, $tableName)
    {
        parent::__construct($driver);
        $this->tableName = $tableName;
    }    
    
    /**
     * Get argument name
     *
     * @access public
     * @return string
     */
    protected function getArgName(string $column): string
    {
         return '_' . str_replace('.', '_', $column);
    }

    /**
     * Prepare the INSERT query
     * 
     * @access public
     * @param string    column names
     *
     * @return $this
     */
    public function prepare()
    {
        // Define column parameters
        $columns = func_get_args();
        if (!empty($columns)){

            // clear current parameters
            unset($this->parameters);
            $this->parameters = array();
            foreach ($columns as $column) {
                $this->parameters[$column] = null;
            }        
        }

        // build query
        parent::prepare();       
        return $this;
    }
    
    /**
     * Bind values parameters
     *
     * @access public
     * @return void
     */
    public function bindValues(): void
    {
        foreach ($this->parameters as $key => $val) {
            $arg = $this->getArgName($key);
            $this->pdoParameters[$arg] = $val;
        }
        parent::bindValues();
    }   

    /**
     * Set a Name/Value Parameter
     *
     * @access public
     * @param string        $columName          The column name
     * @param mixed         $value              The column value
     *
     * @return $this
     */
    public function setValue(string $columName, $value)
    {
        $this->parameters[$columName] = $value;
        return $this;
    }

    /**
     * Set a list of Name/Value Parameters
     *
     * @access public
     * @param array       $values              The key/values array
     * 
     * @return $this
     */
    public function values(array $values)
    {
        foreach ($values as $key => $val) {
            $this->SetValue($key, $val);
        }
        return $this;    
    }

    /**
     * Returns the number of rows affected by the last SQL statement
     * 
     * This function returns false if there is no executed query.
     *
     * @access public
     * @return int|false     The number of affected rows if any, otherwise false.
     */
    public function lastId()
    {
        return (empty(!$this->pdoStatement)) ? $this->driver->lastInsertedId() : false;
    }
    
}