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

namespace Kristuff\Patabase;

use Kristuff\Patabase\Driver;
use Kristuff\Patabase\Datasource;
use Kristuff\Patabase\Query;
use Kristuff\Patabase\Query\CreateTable;
use Kristuff\Patabase\Query\Delete;
use Kristuff\Patabase\Query\Insert;
use Kristuff\Patabase\Query\Select;
use Kristuff\Patabase\Query\Update;

/**
 * Class Database
 *
 * Represents a connection to SQL database
 */
class Database extends Datasource
{
    
    /**
     * Open a database connection
     *
     * @access protected
     * @return void
     */
    protected function openConnection(): void
    {
        $this->driver = Driver\DriverFactory::getInstance($this->settings, false);
    }

    /**
     * Get a new instance of Table object
     *
     * @access public
     * @param string    $tableName      The name of the table
     *
     * @return Table
     */
    public function table($tableName): Table
    {
        return new Table($this, $tableName);
    }

    /**
     * Get a new CreateTable instance 
     *
     * @access public
     * @param string    $tableName      The name of the table
     * @return Query\CreateTable
     */
    public function createTable($tableName): CreateTable
    {
        return new Query\CreateTable($this->getDriver(), $tableName);
    }

    /**
     * Checks if the a table exists
     * 
     * @access public
     * @param string    $tableName      The name of the table
     *
     * @return bool     True if the table exists, otherwise false.
     */
    public function tableExists($tableName): bool
    {
        $sql = trim(sprintf('SELECT 1 FROM %s LIMIT 1', $tableName));
        try {
            $query = $this->getConnection()->prepare($sql);
            return $query->execute();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Drop a table
     *
     * @access public
     * @param string    $tableName      The name of the table
     * 
     * @return bool     True if the table has been dropped, otherwise false
      */
    public function dropTable(string $tableName): bool
    {
        $sql = trim(sprintf('DROP TABLE %s', $tableName));
        return $this->driver->prepareAndExecuteSql($sql);
    }
    
    /**
     * Rename a table
     *
     * @access public
     * @param string    $currentName     The current name of the table to rename
     * @param string    $newName         The new table name
     *
     * @return bool     True if the table has been renamed, otherwise false.
     */
    public function renameTable(string $currentName, string $newName): bool
    {
        $sql = trim(sprintf('ALTER TABLE %s RENAME TO %s', $currentName, $newName));
        return $this->driver->prepareAndExecuteSql($sql);
    }

    /**
     * Get a list of tables in the current database
     *
     * @access public
     * @return array    A non indexed array containing tables names
     */
    public function getTables(): array
    {
        $sql = $this->driver->sqlShowTables();
        $query = $this->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Enable foreign keys
     *
     * @access public
     * @return void
     */
    public function enableForeignKeys(): void
    {
        $this->driver->enableForeignKeys();
    }

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    public function disableForeignKeys(): void
    {
        $this->driver->disableForeignKeys();
    }

    /**
     * Add a foreign key
     * 
     * @access public
     * @param string    $fkName          The constraint name
     * @param string    $srcTable        The source table
     * @param string    $srcColumn       The source column 
     * @param string    $refTable        The referenced table
     * @param string    $refColumn       The referenced column
     *
     * @return bool     True if the foreign key has been added, otherwise false
     */
    public function addForeignKey(string $fkName, string $srcTable, string $srcColumn, string $refTable, string $refColumn): bool
    {
        return $this->driver->addForeignKey($fkName, $srcTable, $srcColumn, $refTable, $refColumn);
    }

    /**
     * Drop a foreign key
     * 
     * @access public
     * @param string    $fkName         The constraint name
     * @param string    $tableName      The source table
     *
     * @return bool     True if the foreign key has been dropped, otherwise false
     */
    public function dropForeignKey(string $fkName, string $tableName): bool
    {
        return $this->driver->dropForeignKey($fkName, $tableName);
    }

    /**
     * Get a new Insert query instance
     *
     * @access public
     * @param string   $tableName      The table in wich insert to
     *
     * @return Query\Insert
     */
    public function insert(string $tableName): Insert
    {
        $query = new Query\Insert($this->driver, $tableName);
        return $query;
    }

    /**
     * Get a new Select query instance
     *
     * @access public
     * @param mixed        
     *
     * @return Query\Select
     */
    public function select(): Select
    {
        $args = func_get_args();
        $query = new Query\Select($this->driver, null, $args);
        return $query;
    }

    /**
     * Get a new Update query instance
     *
     * @access public
     * @param string   $tableName      The name of the table
     *
     * @return Query\Update
     */
    public function update(string $tableName): Update
    {
        $query = new Query\Update($this->driver, $tableName);
        return $query;
    }
        
    /**
     * Get a new Delete query instance
     *
     * @access public
     * @param string   $tableName      The name of the table
     *
     * @return Query\Delete
     */
    public function delete(string $tableName): Delete
    {
        return new Query\Delete($this->driver, $tableName);
    }

    /**
     * Get whether the driver is in transaction
     *
     * @access public
     * @return bool     True if the driver is in transaction, otherwise false
     */
    public function inTransaction(): bool
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Begin a transaction
     *
     * @access public
     * @return void
     */
    public function beginTransaction(): void
    {
        if (! $this->inTransaction()) {
            $this->getConnection()->beginTransaction();
        }
    }

    /**
     * Commit a transaction
     *
     * @access public
     * @return void
     */
    public function commit(): void
    {
        if ($this->inTransaction()) {
            $this->getConnection()->commit();
        }
    }

    /**
     * Rollback a transaction
     *
     * @access public
     * @return void
     */
    public function rollback(): void
    {
        $this->getConnection()->rollback();
    }   
}