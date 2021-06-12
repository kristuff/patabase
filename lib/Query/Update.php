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

use Kristuff\Patabase\Query;
use Kristuff\Patabase\Query\InsertBase;

/**
 * Class Update
 *
 * Represents an [UPDATE] SQL query
 *
 *      UPDATE  [tablename] 
 *         SET  [column/values]
 *       WHERE  [conditions]
 */
class Update extends InsertBase
{
    /**
     * Increment columns list 
     *
     * @access private
     * @var array
     */
    private $incrementColumns = array();

    /**
     * Decrement columns list
     *
     * @access private
     * @var array
     */
    private $decrementColumns = array();

    /**
     * Get a the SQL UPDATE columns statement 
     *
     * @access private
     * @return string
     */
    private function sqlColumns(): string
    {
        $columns = array();
        foreach ($this->parameters as $key => $val) {
            $arg       = ':_' . str_replace('.', '_', $key); 
            $columns[] = $this->escape($key) . ' =' .$arg; 
            $this->pdoParameters[$arg] = $val; 
        }
        foreach ($this->incrementColumns as $key => $val) {
            $columns[] = $this->escape($key) . ' =' . $this->escape($key) . '+' .$val ; 
        }
        foreach ($this->decrementColumns as $key => $val) {
            $columns[] = $this->escape($key) . ' =' . $this->escape($key) . '-' .$val ; 
        }
        return implode(', ', $columns);
    }

    /**
     * Get a WHERE statement object
     *
     * @access public
     * @return Where
     */
    public function where(): Where
    {
        if (!isset($this->where)){
            $this->where = new Query\Where($this, $this->driver);
        }
        return $this->where; 
    }

    /**
     * Add a WHERE column = value condition
     * It's an alias for ->where()->equal($column, $value)
     *
     * @access public
     * @param string    $column     The column name
     * @param mixed     $value      The condition value
     * 
     * @return $this
     */
    public function whereEqual(string $column, $value)
    {
        $this->where()->equal($column, $value);
        return $this;
    }

    /**
     * Increment a column this given value
     *
     * @access public
     * @param string    $column             The column name, could be Table.ColumnName format
     * @param int       $value              The increment value. Default is 1.
     *
     * @return $this
     */
    public function increment(string $column, int $value = 1)
    {
        $this->incrementColumns[$column] = $value;
        return $this;
    }

    /**
     * Decrement a column this given value
     *
     * @access public
     * @param string    $column             The column name, could be Table.ColumnName format
     * @param int       $value              The decrement value. Default is 1.
     *
     * @return $this
     */
    public function decrement(string $column, int $value = 1)
    {
        $this->decrementColumns[$column] = $value;
        return $this;
    }

    /**
     * Build the SQL UPDATE query
     *
     * @access public
     * @return string
     */
    public function sql(): string
    {
        $sqlWhere = (isset($this->where)) ? $this->where->sql() : '';
        $sqlTableName = $this->escape($this->tableName);
        return trim(sprintf('UPDATE %s SET %s %s',
           $sqlTableName,
           $this->sqlColumns(),
           $sqlWhere
        ));
    }
}