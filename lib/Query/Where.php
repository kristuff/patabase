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

use Kristuff\Patabase;
use Kristuff\Patabase\Query\QueryBuilder;
use Kristuff\Patabase\Query\QueryFilter;
use Kristuff\Patabase\Query\Select;
use Kristuff\Patabase\Query\WhereBase;
use Kristuff\Patabase\Driver\DatabaseDriver;

/**
 * Class Where
 *
 * Handle SQL [WHERE] conditions
 */
class Where extends WhereBase
{
    /**
     * Equal to condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  mixed    $value      The condition value
     * @return $this|QueryBuilder  
     */
    public function equal($column, $value)
    {
        $this->addCondition('EQUAL', $this->query->escape($column).' = ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * NotEqual to condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  mixed    $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function notEqual($column, $value)
    {
        $this->addCondition('NOT_EQUAL', $this->query->escape($column).' != ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Greater than condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  mixed    $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function greater($column, $value)
    {
        $this->addCondition('SUP', $this->query->escape($column).' > ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Greater than or equal condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  mixed    $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function greaterEqual($column, $value)
    {
        $this->addCondition('SUP_EQUAL', $this->query->escape($column).' >= ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Lower than condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  mixed    $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function lower($column, $value)
    {
        $this->addCondition('INF', $this->query->escape($column).' < ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * Lower than or equal condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  mixed    $value      The condition value
     *
     * @return $this|QueryBuilder  
     */
    public function lowerEqual($column, $value)
    {
        $this->addCondition('INF_EQUAL', $this->query->escape($column).' <= ', $column, $value);
        return $this->returnFunction();
    }

    /**
     * IS NULL condition
     *
     * @access public
     * @param  string   $column     The column name
     *
     * @return $this|QueryBuilder  
     */
    public function isNull($column)
    {
        $this->addCondition('NULL', $this->query->escape($column).' IS NULL ', $column, null);
        return $this->returnFunction();
    }

    /**
     * IS NOT NULL condition
     *
     * @access public
     * @param  string   $column     The column name
     *
     * @return $this|QueryBuilder  
     */
    public function notNull($column)
    {
        $this->addCondition('NOT_NULL', $this->query->escape($column).' IS NOT NULL ', $column, null);
        return $this->returnFunction();
    }

    /**
     * IN condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  array    $values     The conditions values
     *
     * @return $this|QueryBuilder  
     */
    public function in($column, array $values)
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
     * @param  string   $column     The column name
     * @param  array    $values     The conditions values
     *
     * @return $this|QueryBuilder  
     */
    public function notIn($column, array $values)
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
     * @param  string   $column     The column name
     * @param  string   $pattern    The condition pattern
     *
     * @return $this|QueryBuilder  
     */
    public function like($column, $pattern)
    {
        $this->addCondition('LIKE', $this->query->escape($column).' LIKE ', $column, $pattern);
        return $this->returnFunction();
    }

    /**
     * NOT LIKE condition
     *
     * @access public
     * @param  string   $column     The column name
     * @param  string   $pattern    The condition pattern
     *
     * @return $this|QueryBuilder  
     */
    public function notLike($column, $pattern)
    {
        $this->addCondition('NOT_LIKE', $this->query->escape($column).' NOT LIKE ', $column, $pattern);
        return $this->returnFunction();
    } 
}