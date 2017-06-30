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

namespace Kristuff\Patabase;

use Kristuff\Patabase;
use Kristuff\Patabase\Database;
use Kristuff\Patabase\Query;

/**
 * Class Table 
 */
class Table
{
    /**
     * @access protected
     * @var    Database $database           The Database parent
     */
    protected $database;

    /**
     * @access protected
     * @var    string   $name               The Table Name
     */
    protected $name;

    /**
     * Constructor
     *
     * @access public
     * @param  Database $database           The Database instance
     * @param  string   $tableName          The table name
     */
    public function __construct(Database $database, $tableName)
    {
        $this->database = $database;                 
        $this->name = $tableName;                 
    }

    /**
     * Gets/Returns the table name
     *
     * @access public
     * @return string
     */
    public function name()
    {
        return $this->name;        
    }

    /**
     * Get a new Select query instance
     *
     * @access public
     * @param  array|string(s) (optional)  Column name(s), array of columns name / alias
     *    
     * @return Query\Table\Select
     */
    public function select()
    {
        $args = func_get_args();
        $query = new Query\Select($this->database->getDriver(), null, $args);
        $query->from($this->name);
        return $query;
    }

    /**
     * Get a new Update query instance
     *
     * @access public
     * @param  array    $parameters (optional)     Array of columns name / values
     * @return Query\Table\Update
     */
    public function update(array $parameters = array())
    {
        $query = new Query\Update($this->database->getDriver(), $this->name);
        foreach ($parameters as $key => $val) {
            $query->setValue($key,  $val);
        }
        return $query;
    }

    /**
     * Get a new Delete query instance
     *
     * @access public
     * @return Query\Table\Delete
     */
    public function delete()
    {
        return new Query\Delete($this->database->getDriver(), $this->name);
    }
    
    /**
     * Get a new Insert query instance
     *
     * @access public
     * @param  array    $parameters (optional)     Array of columns name / values
     *
     * @return Query\Table\Insert
     */
    public function insert(array $parameters = array())
    {
        $query = new Query\Insert($this->database->getDriver(), $this->name);
        foreach ($parameters as $key => $val) {
            $query->setValue($key,  $val);
        }
        return $query;
    }

    /**
     * Get a new CreateTable query instance
     *
     * @access public
     * @return Query\Table\Create
     */
    public function create()
    {
        return new Query\CreateTable($this->database->getDriver(), $this->name);
    }

    /**
     * Checks if the table exists
     * 
     * @access public
     * @return bool     True if the table exists, otherwise false.
     */
    public function exists()
    {
        return $this->database->tableExists($this->name);
    }
    
    /**
     * Drop the table
     *
     * @access public
     * @return bool     True if the table has been dropped, otherwise false.
     */
    public function drop()
    {
        return $this->database->dropTable($this->name);
    }
 
    /**
     * Rename the table
     *
     * @access public
     * @param  string   $newName        The new table name
     *
     * @return bool     True if the table has been renamed, otherwise false.
     */
    public function rename($newName)
    {
       $result = $this->database->renameTable($this->name, $newName);
       if ($result){
            $this->name = $newName;
        }
        return $result;
    }
}