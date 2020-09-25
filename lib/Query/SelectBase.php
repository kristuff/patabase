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

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase\Query;
use Kristuff\Patabase\Query\QueryBuilder;

/**
 * Class SelectBase
 * 
 * Abstract base class for Select
 */
abstract class SelectBase extends QueryBuilder
{

    /**
     * Use DISTINCT or not?
     *
     * @access protected
     * @var    boolean
     */
    protected $distinct = false;

    /**
     * Columns list for SELECT query
     *
     * @access protected
     * @var    array
     */
    protected $columns = array();

    /**
     * Table source for SELECT query
     *
     * @access protected
     * @var    array
     */
    protected $fromTable = '';

    /**
     * SQL JOINS internal list
     *
     * @access protected
     * @var    array
     */
    protected $joins = array();

    /**
     * SQL GROUP BY internal list
     *
     * @access protected
     * @var    array
     */
    protected $groupBy = array();

    /**
     * SQL ORDER BY internal list
     *
     * @access protected
     * @var    array
     */
    protected $orderBy = array();

    /**
     * Limit for the SELECT query
     *
     * @access protected
     * @var    int
     */
    protected $limit = 0;

    /**
     * Offset for the SELECT query
     *
     * @access protected
     * @var    int
     */
    protected $offset = 0;

    /**
     * The top QueryBuilder instance, in case of subquery
     *
     * @access protected
     * @var    QueryBuilder
     */
     protected $topQuery = null;

    /**
     * Constructor
     *
     * @access public
     * @param  Driver\DatabaseDriver   $driver   The driver instance
     * @param  Query        $query    The top query parent in case of subquery. Default is NULL
     * @param  array        $args     Columns arguments. Default is empty array
     */
    public function __construct($driver, $query = null, $args = array())
    {
        parent::__construct($driver);
        $this->topQuery = $query;

        // columns arguments
        if (! empty($args)) {
            $this->parseColumnsArguments($args);
        }
    }

   /**
     * Parse the columns arguments for the select query
     *
     * @access protected
     * @param  mixed        $args       The output columns argument
     *
     * @return void
     */
    protected function parseColumnsArguments(array $args)
    {
        // args could be list of name, or one argument indexed array name => alias
        $cols = (count($args) === 1 && is_array($args[0])) ? $args[0] : $args;

        // parse column
        foreach ($cols as $key => $value){
            
            // Each arg could be a non indexed array of name, or 
            // an indexed array name => alias
            $column = !is_int($key) ? $key : $value;
            $columnValue = !is_int($key) ? $value : null;
            $this->column($column, $columnValue);
        }
    }

    /**
     * Define the used of DISTINCT keyword 
     *
     * @access public
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Add an output column for the select
     *
     * @access public
     * @param  string   $column    The column name, could be Table.ColumnName format
     * @param  string   $alias     The alias for this column
     * 
     * @return $this
     */
    public function column($column, $alias = null)
    {
        $this->columns[] = array(
            'type'  => 'column',
            'name'  => $column, 
            'alias' => $alias);
        return $this;
    }

    /**
     * Define the outputs columns for the select
     *
     * @access public
     * @return $this
     */
    public function columns()
    {
        // args could be list of name, of one argument indexed array name => alias
        $args = func_get_args();
        $this->parseColumnsArguments($args);
        return $this;
    }

    /**
     * Add a COUNT(*) column for the select
     *
     * @access public
     * @param  string   $alias     The alias for this column
     * 
     * @return $this
     */
    public function count($alias)
    {
         $this->columns[] = array(
            'type'  => 'count',
            'alias' => $alias
        );
        return $this;
    }

    /**
     * Add a SUM(column) for the select
     *
     * @access public
     * @param  string $column   The column to sum
     * @param  string $alias    The alias for this column 
     * @return $this
     */
    public function sum($column, $alias)
    {
         $this->columns[] = array(
            'type'  => 'sum',
            'name'  => $column,
            'alias' => $alias
        );
        return $this;
    }

    /**
     * Add a MIN(column) for the select
     *
     * @access public
     * @param  string $column   The column to sum
     * @param  string $alias    The alias for this column 
     * @return $this
     */
    public function min($column, $alias)
    {
         $this->columns[] = array(
            'type'  => 'min',
            'name'  => $column,
            'alias' => $alias
        );
        return $this;
    }

    /**
     * Add a MAX(column) for the select
     *
     * @access public
     * @param  string $column   The column to sum
     * @param  string $alias    The alias for this column 
     * @return $this
     */
    public function max($column, $alias)
    {
         $this->columns[] = array(
            'type'  => 'max',
            'name'  => $column,
            'alias' => $alias
        );
        return $this;
    }

    /**
     * Create and returns a new sub Select instance
     *
     * @access public
     * @param  string $alias    The alias for this query 
     *
     * @return Query\Select 
     */
    public function select($alias)
    {
        $query = new Select($this->driver, $this);
        $this->columns[] = array(
            'type' => 'sub_query',
            'query' => $query,
            'alias' => $alias,
        );
        return $query;
    }

    /**
     * Define the FROM tableName
     *
     * @access public
     * @param  string   $tableName      The table name
     *
     * @return $this
     */
    public function from($tableName)
    {
        $this->fromTable = $tableName;
        return $this;       
    }
   
    /**
     * Left join
     *
     * @access public
     * @param  string   $externalTable    Join table
     * @param  string   $externalColumn   Foreign key on the join table
     * @param  string   $localTable       Local table
     * @param  string   $localColumn      Local column
     *
     * @return $this
     */
    public function leftJoin($externalTable, $externalColumn, $localTable, $localColumn)
    {
        $this->joins[] = sprintf(
            'LEFT OUTER JOIN %s ON %s=%s',
            $this->driver->escape($externalTable),
            $this->driver->escape($localTable).'.'.$this->driver->escape($localColumn),
            $this->driver->escape($externalTable).'.'.$this->driver->escape($externalColumn)
        );
        return $this;
    }

    /**
     * Right join
     *
     * @access public
     * @param  string   $externalTable    Join table
     * @param  string   $externalColumn   Foreign key on the join table
     * @param  string   $localTable       Local table
     * @param  string   $localColumn      Local column
     *
     * @return $this
     */
    public function rightJoin($externalTable, $externalColumn, $localTable, $localColumn)
    {
        $this->joins[] = sprintf(
            'RIGHT OUTER JOIN %s ON %s=%s',
            $this->driver->escape($externalTable),
            $this->driver->escape($localTable).'.'.$this->driver->escape($localColumn),
            $this->driver->escape($externalTable).'.'.$this->driver->escape($externalColumn)
        );
        return $this;
    }

    /**
     * Full join
     *
     * @access public
     * @param  string   $externalTable    Join table
     * @param  string   $externalColumn   Foreign key on the join table
     * @param  string   $localTable       Local table
     * @param  string   $localColumn      Local column
     *
     * @return $this
     */
    public function fullJoin($externalTable, $externalColumn, $localTable, $localColumn)
    {
        $this->joins[] = sprintf(
            'FULL OUTER JOIN %s ON %s=%s',
            $this->driver->escape($externalTable),
            $this->driver->escape($localTable).'.'.$this->driver->escape($localColumn),
            $this->driver->escape($externalTable).'.'.$this->driver->escape($externalColumn)
        );
        return $this;
    }       
    
    /**
     * Inner join
     *
     * @access public
     * @param  string   $externalTable    Join table
     * @param  string   $externalColumn   Foreign key on the join table
     * @param  string   $localTable       Local table
     * @param  string   $localColumn      Local column
     *
     * @return $this
     */
    public function innerJoin($externalTable, $externalColumn, $localTable, $localColumn)
    {
        $this->joins[] = sprintf(
            'INNER JOIN %s ON %s=%s',
            $this->driver->escape($externalTable),
            $this->driver->escape($localTable).'.'.$this->driver->escape($localColumn),
            $this->driver->escape($externalTable).'.'.$this->driver->escape($externalColumn)
        );
        return $this;
    }

    /**
     * join (alias for innerJoin)
     *
     * @access public
     * @param  string   $externalTable    Join table
     * @param  string   $externalColumn   Foreign key on the join table
     * @param  string   $localTable       Local table
     * @param  string   $localColumn      Local column
     *
     * @return $this
     */
    public function join($externalTable, $externalColumn, $localTable, $localColumn)
    {
        return $this->innerJoin($externalTable, $externalColumn, $localTable, $localColumn);
    }

    /**
     * Get a WHERE statement object
     *
     * @access public
     * @return Query\Where
     */
    public function where()
    {
        if (!isset($this->where)){
            $this->where = new Query\Where($this, $this->driver, $this->topQuery);
        }
        return $this->where; 
    }

    /**
     * Add a WHERE column = value condition
     * It's an alias for ->where()->equal($column, $value)
     * 
     * @access public
     * @return Query\Where
     */
    public function whereEqual($column, $value)
    {
        $this->where()->equal($column, $value);
        return $this;
    }
    
    /**
     * Get an HAVING statement object
     *
     * @access public
     * @return Query\Having
     */
    public function having()
    {
        if (!isset($this->having)){
            $this->having = new Query\Having($this, $this->driver, $this->topQuery);
        }
        return $this->having; 
    }

    /**
     * Define the GROUP BY 
     *
     * @access public
     * @param  mixed 
     * @return $this
     */
    public function groupBy()
    {
        $this->groupBy = func_get_args();
        return $this;
    }
        
    /**
     * Add an ORDER BY statement
     *
     * @access public
     * @param  string   $column    Column name
     * @param  string   $order     Direction ASC or DESC or custom function
     *
     * @return $this
     */
    public function orderBy($column, $order = self::SORT_ASC)
    {
        $this->orderBy[] = array(
            'column' => $column,
            'order'  => $order
        );
        return $this;
    }
        
    /**
     * Add an ORDER BY [X] ASC statement
     *
     * @access public
     * @param  string   $column    The column name
     * @return $this
     */
    public function orderAsc($column)
    {   
        $this->orderBy($column, self::SORT_ASC);
        return $this;
    }

    /**
     * Add an ORDER BY [X] DESC statement
     *
     * @access public
     * @param  string   $column    The column name
     * @return $this
     */
    public function orderDesc($column)
    {
        $this->orderBy($column, self::SORT_DESC);
        return $this;
    }

    /**
     * Add an ORDER BY *random function* statement
     *
     * @access public
     * @param  int      $seed    (optional) The random seed.
     *
     * @return $this
     */
    public function orderRand($seed = null)
    {
        $this->orderBy(NULL, $this->driver->sqlRandom($seed));
        return $this;
    }

    /**
     * Define the query LIMIT
     *
     * @access public
     * @param  int       $value    The limit value
     * 
     * @return $this
     */
    public function limit($value)
    {
        if (! is_null($value)) {
            $this->limit = (int) $value;
        }
        return $this;
    }

    /**
     * Define the query OFFSET
     *
     * @access public
     * @param  int      $value      The offset value
     *
     * @return $this
     */
    public function offset($value)
    {
        if (! is_null($value)) {
            $this->offset = (int) $value;
        }
        return $this;
    }
}