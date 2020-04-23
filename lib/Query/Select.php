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
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase;
use Kristuff\Patabase\Database;
use Kristuff\Patabase\Query;
use Kristuff\Patabase\Query\QueryBuilder;
use Kristuff\Patabase\Query\SelectBase;
use Kristuff\Patabase\Exception;

/**
 * Class Select
 * 
 * Represents a [SELECT] SQL query
 *
 *      SELECT  [distinct?] [columnsdefintions]
 *        FROM  [tablename] 
 *        JOIN  [externaltables]
 *       WHERE  [conditions]
 *    GROUP BY  [expressions]
 *    ORDER BY  [expressions]
 *       LIMIT  [limit]
 *      OFFSET  [offset]
 */
class Select extends SelectBase
{
  
    
    /**
     * Get an argument name based on column name
     * Make sure the argument name is unique to Avoid collision in query parameters.
     *
     * @access protected
     * @param  string   $column     The column name or base name
     *
     * @return string
     */
    protected function getArgumentName($column)
    {
        $topQuery = $this->topQuery ?: $this;  
        $arg = ':_' . str_replace('.', '_', $column); 
        return $topQuery->sqlParameterExists($arg) ? $arg . uniqid() : $arg;
    }
    
    /**
     * Get the SQL SELECT [COLUMNS] statement 
     *
     * @access private
     * @return string
     */
    private function sqlColumns()
    {
        // no columns givens select all (SELECT *)
        if (!count($this->columns) > 0 ) {
            return '*';   
        }

        // use DISTINCT ?
        $sqlDistinct    = $this->distinct ? 'DISTINCT ': '';

        // parse columns
        $colsList = array();
        foreach ($this->columns as $val){
            switch ($val['type']){
                   
                // 'classic' column
                case 'column':
                    $name       = $this->escape($val['name']);
                    $alias      = isset($val['alias']) ? 'AS '. $this->escape($val['alias']) : '';
                    $colsList[] = trim(sprintf('%s %s', $name, $alias));
                    break;  
                   
                // COUNT() column
                case 'count':
                    $colsList[] = trim(sprintf('COUNT(*) AS %s', $this->escape($val['alias'])));
                    break;  

                // SUM() column
                case 'sum':
                    $name       = $this->escape($val['name']);
                    $alias      = isset($val['alias']) ? 'AS '. $this->escape($val['alias']) : '';
                    $colsList[] = sprintf('SUM(%s)', $name) . $alias;
                    break;  

                // sub query
                case 'sub_query':
                    $colsList[] = '('. $val['query']->sql() .') AS '. $this->escape($val['alias']);
                    break;
           }
        }
        return $sqlDistinct . implode(', ', $colsList);
    }
    
    /**
     * Build the SQL [SELECT] query
     *
     * @access public
     * @return string
     */
    public function sql()
    {
        $topQuery = $this->topQuery ?: $this;  
        $sqlJoins = empty($this->joins) ? '' : implode(' ', $this->joins) ; 
        $sqlFromTable =  'FROM '. $this->escape($this->fromTable);
        $sqlWhere = isset($this->where) ? $this->where->sql() : '';
        $sqlGroupBy = empty($this->groupBy) ? '' : 'GROUP BY '.implode(', ', $this->escapeList($this->groupBy));
        $sqlHaving = isset($this->having) ? $this->having->sql() : '';

        // order by
        $sqlOrderBy = '';
        if (! empty($this->orderBy)){
            $sortArgs = [];
            foreach ($this->orderBy as $item){
                $sql = $item['column'] ? $this->escape($item['column']) . ' ' : '';
                $sortArgs[] = $sql . $item['order'];
            }
            $sqlOrderBy = 'ORDER BY ' . implode(', ', $sortArgs);
        }

        // limit
        $sqlLimit = '';
        if ($this->limit > 0){
            $argName = $this->getArgumentName('LIMIT');   
            $topQuery->setSqlParameter($argName, $this->limit); 
            $sqlLimit = 'LIMIT '.$argName;
        }

        // offset
        $sqlOffset = '';
        if ($this->offset > 0){
            $argName = $this->getArgumentName('OFFSET');   
            $topQuery->setSqlParameter($argName, $this->offset); 
            $sqlOffset = 'OFFSET ' . $argName;
        }

        return trim(implode(' ', ['SELECT', 
           $this->sqlColumns(), 
           $sqlFromTable,
           $sqlJoins,
           $sqlWhere,
           $sqlGroupBy,
           $sqlHaving,
           $sqlOrderBy,
           $sqlLimit,
           $sqlOffset]));
    }
     
    /**
     * Execute the select query and returns result in given format
     *
     * @access  public
     * @param   string      $outputFormat       The output format
     *
     * @return  mixed
     */
    public function getAll(string $outputFormat = 'default')
    {
        // execute query
       $exec = $this->execute();

       // format
       $format = ($outputFormat === 'default') ? $this->driver->defaultOutputFormat() : $outputFormat; 

        // return output
        return QueryBuilder::fetchOutput($this, $exec, $format);
    }

    /**
     * Execute the select query and returns the result limited to one row.
     *
     * @access  public
     * @param   string      $outputFormat       The output format
     * 
     * @return  mixed
     */
    public function getOne(string $outputFormat = 'default')
    {
        $this->limit(1);
        return $this->getAll($outputFormat);
    }

    /**
     * Execute the select query and returns the result of the first column in first row.
     *
     * @access  public
     * @param   string      $outputFormat       The output format
     *
     * @return  mixed|null
     */
    public function getColumn()
    {
        $this->limit(1);
        return $this->execute() ? $this->pdoStatement->fetchColumn() : NULL ;
    }

}