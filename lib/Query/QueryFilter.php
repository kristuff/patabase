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

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase\Query;
use Kristuff\Patabase\Query\QueryBuilder;
use Kristuff\Patabase\Driver\DatabaseDriver;
use Kristuff\Patabase\Exception;

/**
 * Class QueryFilter
 *
 * Abstract base class for WHERE and HAVING statements
 */
abstract class QueryFilter
{

    /**
     * QueryBuilder instance
     *
     * @access protected
     * @var    Query\QueryBuilder
     */
    protected $query;

    /**
     * Top QueryBuilder instance (in case of subquery)
     *
     * @access protected
     * @var    Query\QueryBuilder
     */
    protected $topQuery = null;

    /**
     * Driver instance
     *
     * @access protected
     * @var    Driver\DatabaseDriver
     */
    protected $driver;

    /**
     * List of WHERE/HAVING conditions
     *
     * @access private
     * @var    array
     */
    protected $conditions = array();

    /**
     * Get whether a group (AND / OR) is open
     *
     * @access private
     * @var    bool
     */
    protected $isGroupOpen = false;

    /**
     * sql base: WHERE or HAVING
     *
     * @access protected
     * @var    string
     */
    protected $sqlBase = '';

    /**
     * Internal method to add condition
     *
     * @access private
     * @param string    $type    
     * @param string    $column  
     * @param string    $sql     
     * @param mixed     $value
     * @param string    $operator  
     *
     * @return void
     */
    protected function addCondition($type, $sql, $column, $value, $operator = '')
    {
        $this->conditions[] = array(
            'type'     =>  $type,
            'sql'      =>  $sql,
            'column'   =>  $column,
            'value'    =>  $value,
            'operator' =>  $operator,
        );
    }

    /**
     * Get an argument name based on column name
     * Make sure the argument name is unique to Avoid collision in query parameters.
     *
     * @access private
     * @param  string   $column     The column name
     *
     * @return string
     */
    private function getArgumentName($column)
    {
        $arg = ':__' . str_replace('.', '_', $column); 
        return $this->topQuery->sqlParameterExists($arg) ? $arg . uniqid() : $arg;
    }

    /**
     * Constructor
     *
     * @access public
     * @param  Query\QueryBuilder       $query         The QueryBuilder instance
     * @param  Driver\DatabaseDriver    $driver        The Driver instance
     * @param  Query\QueryBuilder       $topQuery      The top QueryBuilder instance in case of sub query (default is null)
     */
    public function __construct(QueryBuilder $query, DatabaseDriver $driver, QueryBuilder $topQuery = null)
    {
        $this->query    = $query;
        $this->topQuery = $topQuery ? $topQuery : $query;
        $this->driver   = $driver;
    }

    /**
     * Begin OR group condition
     *
     * @access public
     * @return $this 
     */
    public function beginOr()
    {
        $this->addCondition('group_start', '(', null, null, 'OR');
        $this->isGroupOpen = true;
        return $this;
    }

    /**
     * Close OR group condition
     *
     * @access public
     * @return $this 
     */
    public function closeOr()
    {
        return $this->closeGroup();
    }

    /**
     * Begin AND group condition
     *
     * @access public
     * @return $this
     */
    public function beginAnd()
    {
        $this->addCondition('group_start', '(', null, null, 'AND');
        $this->isGroupOpen = true;
        return $this;
    }

    /**
     * Close AND group condition
     *
     * @access public
     * @return QueryBuilder
     */
    public function closeAnd()
    {
        return $this->closeGroup();
    }

    /**
     * Close group condition
     *
     * @access public
     * @return QueryBuilder
     */
    public function closeGroup()
    {
        $this->addCondition('group_end', ')', null, null);
        $this->isGroupOpen = false;
        return $this->query;
    }

    /**
     * Return function
     *
     * @access protected
     * @return $this|QueryBuilder
     */
    protected function returnFunction()
    {
         return $this->isGroupOpen === true ? $this : $this->query;
    }

    /**
     * Construct and returns the sql for IN or NOT IN statement
     *
     * @access protected
     * @param  array    $item       The filter item
     *
     * @return string
     */
    protected function getSqlInOrNotIn(array $item)
    {
        // define argument for each values
        $valueArgs = array();
        foreach($item['value'] as $value){
            $arg = $this->getArgumentName($item['column']); 
            $valueArgs[] = $arg;

            // set parameters
            $this->topQuery->setSqlParameter($arg, $value); 
        }
        
        //build and return sql
        return $item['sql']. '(' . implode(', ', $valueArgs) .')';
    }

    /**
     * Build sql and parameters
     *
     * @access public
     * @return string 
     */
    public function sql()
    {
        $sql = '';
        if (!empty($this->conditions)) {
            $sql = ' '. $this->sqlBase  .' ';  // start the SQL WHERE or HAVING clause
            $currentOperator = 'AND';          // current condition operator
            
            foreach ($this->conditions as $key => $item) {

                // need operator AND or OR, except for the first or if 
                // previous item is a begin group item
                $isSqlNeedOperator = $key > 0  && $this->conditions[$key -1]['type'] != 'group_start';

                switch ($item['type']) {
                    
                    case'group_start':
                        $currentOperator = $item['operator']; // register operator
                        $sql .= '(' ;
                        break;
                    
                    case'group_end':
                        $currentOperator = 'AND'; // reset operator
                        $sql .= ')';
                        break;

                   case 'NULL':
                   case 'NOT_NULL':
                        $sql .=  $isSqlNeedOperator ? ' '.$currentOperator.' ' : '';
                        $sql .=  $item['sql'];
                        
                        break;
                    case 'IN':
                    case 'NOT_IN':
                        $sql .=  $isSqlNeedOperator ? ' '.$currentOperator.' ' : '';
                        $sql .=  $this->getSqlInOrNotIn($item);
                        break;

                    default:
                        $arg = $this->getArgumentName($item['column']);
                        $sql .=  $isSqlNeedOperator ? ' '.$currentOperator.' ' : '';
                        $sql .=  $item['sql'] . $arg;
                       
                        // set parameters
                        $this->topQuery->setSqlParameter($arg, $item['value']); 
                        break;
                }

            }
        }
        // now return
        return $sql;        
    }
}