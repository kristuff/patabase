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
 * @version    1.0.1
 * @copyright  2017-2022 Christophe Buliard
 */

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase\Query\QueryBuilder;
use Kristuff\Patabase\Query\QueryFilter;

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
     * @param string    $function   The function name without parenthesis  ('SUM', 'COUNT', ...) 
     * @param string    $column     The column name
     * @param string    $operator   The logic operator (example '=', '<', ...)
     * @param  mixed    $value      The condition value 
     *
     * @return $this|QueryBuilder  
     */
    public function fn(string $function, string $column, string $operator, $value)
    {
        $sql = $function .'('. ($column ? $this->query->escape($column): '') . ') ' . $operator . ' ';
        $this->addCondition($function, $sql, $column, $value);
        return $this->returnFunction();
    }

    /**
     * Add an HAVING COUNT() filter 
     *
     * @access public
     * @param string    $operator   The logic operator (example '=', '<', ...)
     * @param mixed     $value      The condition value
     * 
     * @return $this|QueryBuilder  
     */
    public function count(string $operator, $value)
    {
        $sql = 'COUNT(*) '. $operator. ' ';
        $this->addCondition('COUNT', $sql, 'COUNT', $value);
        return $this->returnFunction();
    }

    /**
     * Add an HAVING SUM() filter 
     *
     * @access public
     * @param string    $column     The column name
     * @param string    $operator   The logic operator (example '=', '<', ...)
     * @param mixed     $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function sum(string $column, string $operator, $value)
    {
        $this->fn('SUM', $column, $operator, $value);
        return $this->returnFunction();
    }

}