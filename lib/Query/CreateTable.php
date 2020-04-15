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
* @version    0.2.0
 *
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase;
use Kristuff\Patabase\Database;
use Kristuff\Patabase\Query;
use Kristuff\Patabase\Driver\DatabaseDriver;

/**
 * Class CreateTable 
 *
 * Represents a [CREATE TABLE] SQL query
 */
class CreateTable extends Query\QueryBuilder
{

    /**
     * Supported string keywords for defaults values     
     *
     * @access private
     * @var    string       $supportedDefaults
     */
    private $supportedDefaults = array('NULL', 'CURRENT_TIMESTAMP');

    /**
     * Table name (CREATE TABLE [?])
     *
     * @access private
     * @var    string       $tableName
     */
    private $tableName = null;
         
    /**
     * Columns list 
     *
     * @access private
     * @var    array        $columns
     */
    private $columns = array();

    /**
     * Foreign Keys list 
     *
     * @access private
     * @var    array        $foreignKeys
     */
    private $foreignKeys = array();

    /**
     * Add or not the [If not exist] statement
     *
     * @access private
     * @var    bool
     */
    private $isNotExists = false;
    
    /**
     * Constructor
     *
     * @access public
     * @param  DatabaseDriver   $driver         The driver instance
     * @param  string       $tableName      The table name
     */
    public function __construct(DatabaseDriver $driver, $tableName)
    {
        parent::__construct($driver);
        $this->tableName = $tableName;
    }    
    
    /**
     * Set the IF NOT EXISTS 
     *
     * @access public
     * @return $this
     */
    public function ifNotExists()
    {
        $this->isNotExists = true;
        return $this;
    }

    /**
     * Add a column to the list of column definition
     *
     * @access public
     * @param  mixed column definition
     *
     * @return $this
     */
    public function column()
    {
       $this->columns[] = func_get_args();
       return $this;
    }
     
    /**
     * Add a foreign key contraint
     *
     * @access public
     * @param  string       $fkName         The name for the foreign key
     * @param  string       $srcColumn      The column in main table
     * @param  string       $refTable       The referenced table
     * @param  string       $refColumn      The column in referenced table
     * @param  string       $onUpdate       (optional) The on update rule. Default is CASCADE
     * @param  string       $onDelete       (optional) The on delete rule. Default is RESTRICT
     *
     * @return $this
     */
    public function fk($fkName, $srcColumn, $refTable, $refColumn, $onUpdate = 'CASCADE', $onDelete = 'RESTRICT')
    {
       $this->foreignKeys[] = array(
            'name'          => $fkName,
            'src_column'    => $srcColumn,
            'ref_table'     => $refTable,
            'ref_column'    => $refColumn,
            'on_update'     => $onUpdate,
            'on_delete'     => $onDelete
       );
       return $this;
    }

    /**
     * Get the SQL COLUMNS statement
     *
     * @access public
     * @return string
     */
    private function sqlColumns()
    {
        $result = array();
        foreach ($this->columns as $col){

            // Parse arguments. First item is NAME and second is TYPE
            $sqlName   = $this->escape($col[0]);
            $sqlType   = $col[1];  //TODO check type
            
            // following arguments
            $args       = array_slice($col, 2);
            $currentIndex       = 0;
            $defaultValueIndex  = -1;

            $sqlConstraintUnique    = '';       // UNIQUE ?, not by default
            $sqlConstraintNullable  = 'NULL';   // allow null value by default
            $isPk                   = false;    // PRIMARY KEY?
            $sqlDefault             = '';       // DEFAULT VALUE?

            foreach ($args as $arg){

                // last index was DEFAULT, so the current argument 
                // is the value for default contsaint
                if ($currentIndex === $defaultValueIndex){
                    
                    // string
                    if (is_string($arg)){
                            
                        // escape everything except constants
                        if (in_array(strtoupper($arg), $this->supportedDefaults)){
                            $sqlDefault = 'DEFAULT ' . $arg;
                        } else {
                            $sqlDefault = 'DEFAULT ' . $this->driver->escapeValue($arg);
                        }
                        
                    // int/float are not escaped
                    } elseif (is_int($arg) || is_float($arg)){
                        $sqlDefault = 'DEFAULT ' . $arg;

                    // bool Type
                    } elseif (is_bool($arg)){
                        $sqlDefault = 'DEFAULT ' . ($arg ? 'TRUE' : 'FALSE');                            
                    }


                } else {
                    switch (strtoupper($arg)){
                        
                        // NULL  /NOT NULL 
                        case 'NULL':
                            $sqlConstraintNullable = 'NULL';
                            break;


                        case 'NOT NULL':
                            $sqlConstraintNullable = 'NOT NULL';
                            break;

                        // UNIQUE
                        case 'UNIQUE':
                            $sqlConstraintUnique = 'UNIQUE';
                            break;

                        // AUTO INCREMENT
                        case 'AUTO INCREMENT':
                        case 'AUTO_INCREMENT':
                        case 'AI':
                            $sqlType = $this->driver->sqlColumnAutoIncrement($sqlType);
                            break;                            

                        // PK
                        case 'PRIMARY KEY':
                        case 'PRIMARY_KEY':
                        case 'PK':
                            $isPk = true;
                            break;

                        // DEFAULT
                        case 'DEFAULT':
                            // define next index as the DefaultValue index
                            $defaultValueIndex = $currentIndex +1;
                            break;
                    
                    }
                }

                // update  current index
                $currentIndex ++;
            }

            // set optional params
            // PK ?, UNIQUE ?, NULL? (PK cannot be null), DEFAULT?
            // AI is handle with sqltype
            $result[] = trim(implode(' ', [$sqlName, 
                                           $sqlType, 
                                           $isPk ? 'NOT NULL' : $sqlConstraintNullable,
                                           $isPk ? 'PRIMARY KEY' : '',
                                           $sqlConstraintUnique,
                                           $sqlDefault]));
        }

        // FK CONSTRANT
        foreach ($this->foreignKeys as $foreignKey){
            $result[] =  trim(sprintf('CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s)', 
                            $foreignKey['name'], 
                            $this->driver->escapeIdentifier($foreignKey['src_column']), 
                            $this->driver->escapeIdentifier($foreignKey['ref_table']),
                            $this->driver->escapeIdentifier($foreignKey['ref_column'])
            ));
        }

        return implode(', ', $result);
    }
   
    /**
     * Build the CREATE TABLE query
     *
     * @access public
     * @return string
     */
    public function sql()
    {
        $sqlTableName = $this->driver->escape($this->tableName);
        $sqlIfNotExists =  $this->isNotExists === true ? 'IF NOT EXISTS' : '';

        return trim(sprintf(
            'CREATE TABLE %s %s (%s) %s',
            $sqlIfNotExists,    
            $sqlTableName,
            $this->sqlColumns(),
            $this->driver->sqlCreateTableOptions()
        ));
    }

}