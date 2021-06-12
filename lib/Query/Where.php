<?php

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
use Kristuff\Patabase\Query\QueryFilter;

/**
 * Class Where
 *
 * Handle SQL [WHERE] conditions
 */
class Where extends QueryFilter
{
    /**
     * sql base: WHERE or HAVING
     *
     * @access protected
     * @var string
     */
    protected $sqlBase = 'WHERE';   

    /**
     * Equal to condition
     *
     * @access public
     * @param string    $column     The column name
     * @param mixed     $value      The condition value
     * @return $this|QueryBuilder  
     */
    public function equal(string $column, $value)
    {
        $this->addCondition('EQUAL', $this->query->escape($column).' = ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * NotEqual to condition
     *
     * @access public
     * @param string    $column     The column name
     * @param mixed     $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function notEqual(string $column, $value)
    {
        $this->addCondition('NOT_EQUAL', $this->query->escape($column).' != ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Greater than condition
     *
     * @access public
     * @param string    $column     The column name
     * @param mixed     $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function greater(string $column, $value)
    {
        $this->addCondition('SUP', $this->query->escape($column).' > ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Greater than or equal condition
     *
     * @access public
     * @param string    $column     The column name
     * @param mixed     $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function greaterEqual(string $column, $value)
    {
        $this->addCondition('SUP_EQUAL', $this->query->escape($column).' >= ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Lower than condition
     *
     * @access public
     * @param string    $column     The column name
     * @param mixed     $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function lower(string $column, $value)
    {
        $this->addCondition('INF', $this->query->escape($column).' < ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Lower than or equal condition
     *
     * @access public
     * @param string    $column     The column name
     * @param mixed     $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function lowerEqual(string $column, $value)
    {
        $this->addCondition('INF_EQUAL', $this->query->escape($column).' <= ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * IS NULL condition
     *
     * @access public
     * @param string    $column     The column name
     *
     * @return $this|QueryBuilder  
     */
    public function isNull(string $column)
    {
        $this->addCondition('NULL', $this->query->escape($column).' IS NULL ', $column, null);
        return $this->returnFunction();
    }

    /**
     * IS NOT NULL condition
     *
     * @access public
     * @param string    $column     The column name
     *
     * @return $this|QueryBuilder  
     */
    public function notNull(string $column)
    {
        $this->addCondition('NOT_NULL', $this->query->escape($column).' IS NOT NULL ', $column, null);
        return $this->returnFunction();
    }

    /**
     * IN condition
     *
     * @access public
     * @param string    $column     The column name
     * @param array     $values     The conditions values
     *
     * @return $this|QueryBuilder  
     */
    public function in(string $column, array $values)
    {
        if (! empty($values)) {
            $this->addCondition('IN', $this->query->escape($column).' IN ', $column, $values);
        }
        return $this->returnFunction();
    }

    /**
     * NOT IN condition
     *
     * @access public
     * @param string   $column     The column name
     * @param array    $values     The conditions values
     *
     * @return $this|QueryBuilder  
     */
    public function notIn(string $column, array $values)
    {
        if (! empty($values)) {
            $this->addCondition('NOT_IN', $this->query->escape($column).' NOT IN ', $column, $values);
        }
        return $this->returnFunction();
    }

    /**
     * LIKE condition
     *
     * @access public
     * @param string   $column     The column name
     * @param string   $pattern    The condition pattern
     *
     * @return $this|QueryBuilder  
     */
    public function like(string $column, string $pattern)
    {
        $this->addCondition('LIKE', $this->query->escape($column).' LIKE ', $column, $pattern);
        return $this->returnFunction();
    }

    /**
     * NOT LIKE condition
     *
     * @access public
     * @param string   $column     The column name
     * @param string   $pattern    The condition pattern
     *
     * @return $this|QueryBuilder  
     */
    public function notLike(string $column, string $pattern)
    {
        $this->addCondition('NOT_LIKE', $this->query->escape($column).' NOT LIKE ', $column, $pattern);
        return $this->returnFunction();
    } 
}