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
use Kristuff\Patabase\Query\QueryBuilder;
use Kristuff\Patabase\Query\QueryFilter;
use Kristuff\Patabase\Query\Select;
use Kristuff\Patabase\Driver\DatabaseDriver;

/**
 * Handle SQL HAVING conditions
 */
class Having extends QueryFilter
{

    /**
     * sql base: WHERE or HAVING
     *
     * @access protected
     * @var    string
     */
    protected $sqlBase = 'HAVING';
    
    /**
     * Add an HAVING function filter 
     *
     * @access public
     * @param  string   $function   The function name without parenthesis  ('SUM', 'COUNT', ...) 
     * @param  string   $column     The column name
     * @param  string   $operator   The logic operator (example '=', '<', ...)
     * @param  mixed    $value      The condition value 
     *
     * @return $this|QueryBuilder  
     */
    public function fn($function, $column, $operator, $value)
    {
        $sql = $function .'('. ($column ? $this->query->escape($column): '') . ') ' . $operator . ' ';
        $this->addCondition($function, $sql, $column, $value);
        return $this->returnFunction();
    }

    /**
     * Add an HAVING COUNT() filter 
     *
     * @access public
     * @param  string   $operator   The logic operator (example '=', '<', ...)
     * @param  mixed    $value      The condition value
     * 
     * @return $this|QueryBuilder  
     */
    public function count($operator, $value)
    {
        $sql = 'COUNT(*) '. $operator. ' ';
        $this->addCondition('COUNT', $sql, 'COUNT', $value);
        return $this->returnFunction();
    }

    /**
     * Add an HAVING SUM() filter 
     *
     * @access public
     * @param  string   $column     The column name
     * @param  string   $operator   The logic operator (example '=', '<', ...)
     * @param  mixed    $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function sum($column, $operator, $value)
    {
        $this->fn('SUM', $column, $operator, $value);
        return $this->returnFunction();
    }

}