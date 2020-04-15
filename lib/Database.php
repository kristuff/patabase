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
 * @version    0.1.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase;

use Kristuff\Patabase;
use Kristuff\Patabase\Driver;
use Kristuff\Patabase\Datasource;

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
    protected function openConnection()
    {
        $this->driver = Driver\DriverFactory::getInstance($this->settings, false);
    }

    /**
     * Get a new instance of Table object
     *
     * @access public
     * @param  string       $tableName      The name of the table
     *
     * @return Table
     */
    public function table($tableName)
    {
        return new Table($this, $tableName);
    }

    /**
     * Get a new CreateTable instance 
     *
     * @access public
     * @param  string       $tableName      The name of the table
     * @return Query\CreateTable
     */
    public function createTable($tableName)
    {
        return new Query\CreateTable($this->getDriver(), $tableName);
    }

    /**
     * Checks if the a table exists
     * 
     * @access public
     * @param  string       $tableName      The name of the table
     *
     * @return bool         True if the table exists, otherwise false.
     */
    public function tableExists($tableName)
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
     * @param  string       $tableName      The name of the table
     * @return bool         True if the table has been dropped, otherwise false
      */
    public function dropTable($tableName)
    {
        $sql = trim(sprintf('DROP TABLE %s', $tableName));
        return $this->driver->prepareAndExecuteSql($sql);
    }
    
    /**
     * Rename a table
     *
     * @access public
     * @param  string   $currentName     The current name of the table to rename
     * @param  string   $newName         The new table name
     *
     * @return bool     True if the table has been renamed, otherwise false.
     */
    public function renameTable($currentName, $newName)
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
    public function getTables()
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
    public function enableForeignKeys()
    {
        $this->driver->enableForeignKeys();
    }

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    public function disableForeignKeys()
    {
        $this->driver->disableForeignKeys();
    }

    /**
     * Add a foreign key
     * 
     * @access public
     * @param  string   $fkName          The constraint name
     * @param  string   $srcTable        The source table
     * @param  string   $srcColumn       The source column 
     * @param  string   $refTable        The referenced table
     * @param  string   $refColumn       The referenced column
     *
     * @return bool     True if the foreign key has been added, otherwise false
     */
    public function addForeignKey($fkName, $srcTable, $srcColumn, $refTable, $refColumn)
    {
        return $this->driver->addForeignKey($fkName, $srcTable, $srcColumn, $refTable, $refColumn);
    }

    /**
     * Drop a foreign key
     * 
     * @access public
     * @param  string   $fkName         The constraint name
     * @param  string   $tableName      The source table
     *
     * @return bool     True if the foreign key has been dropped, otherwise false
     */
    public function dropForeignKey($fkName, $tableName)
    {
        return $this->driver->dropForeignKey($fkName, $tableName);
    }

    /**
     * Get a new Insert query instance
     *
     * @access public
     * @param  string   $tableName      The table in wich insert to
     *
     * @return Query\Table\Insert
     */
    public function insert($tableName)
    {
        $query = new Query\Insert($this->driver, $tableName);
        return $query;
    }

    /**
     * Get a new Select query instance
     *
     * @access public
     * @param  mixed        
     *
     * @return Query\Table\Select
     */
    public function select()
    {
        $args = func_get_args();
        $query = new Query\Select($this->driver, null, $args);
        return $query;
    }

    /**
     * Get a new Update query instance
     *
     * @access public
     * @param  string   $tableName      The name of the table
     *
     * @return Query\Table\Update
     */
    public function update($tableName)
    {
        $query = new Query\Update($this->driver, $tableName);
        return $query;
    }
        
    /**
     * Get a new Delete query instance
     *
     * @access public
     * @param  string   $tableName      The name of the table
     *
     * @return Query\Table\Delete
     */
    public function delete($tableName)
    {
        return new Query\Delete($this->driver, $tableName);
    }

    /**
     * Get whether the driver is in transaction
     *
     * @access public
     * @return bool     True if the driver is in transaction, otherwise false
     */
    public function inTransaction()
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Begin a transaction
     *
     * @access public
     * @return void
     */
    public function beginTransaction()
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
    public function commit()
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
    public function rollback()
    {
        $this->getConnection()->rollback();
    }   
}