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

use Kristuff\Patabase\Driver\DatabaseDriver;
use Kristuff\Patabase\Query;

/**
 * Class Delete
 *
 * Represents a [DELETE FROM] SQL query
 *
 * DELETE FROM  [tablename] 
 *        WHERE [conditions]
 */
class Delete extends Query\QueryBuilder
{
  
    /**
     * Table name (DELETE FROM [?])
     *
     * @access private
     * @var    string
     */
    private $tableName = null;

    /**
     * Constructor
     *
     * @access public
     * @param DatabaseDriver    $driver         The driver instance
     * @param string            $tableName      The table name
     */
    public function __construct(DatabaseDriver $driver, string $tableName)
    {
        parent::__construct($driver);
        $this->tableName = $tableName;
    }    
    
    /**
     * Get a WHERE stamement object
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
     *
     * @access public
     * @param string            $column         The columnn name
     * @param mixed             $value      
     * 
     * @return $this
     */
    public function whereEqual(string $column, $value)
    {
        $this->where()->equal($column, $value);
        return $this;
    }

    /**
     * Build the SQL DELETE query
     *
     * @access public
     * @return string
     */
    public function sql(): string
    {
       // Build sql where and escape table name
       $sqlWhere = (isset($this->where)) ? $this->where->sql() : '';
       $sqlTableName = $this->escape($this->tableName);
       
       // DELETE query
       return trim(sprintf('DELETE FROM %s %s', 
                            $sqlTableName, 
                            $sqlWhere));
    }
       
}
