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
 * @version    0.5.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Query;

/**
 * Class Insert
 *
 * Represents a [INSERT INTO] SQL query
 */
class Insert extends \Kristuff\Patabase\Query\InsertBase
{
    /**
     * Build the SQL INSERT query
     *
     * @access public
     * @return string
     */
    public function sql()
    {
        $columnsNames   = array();
        $columnsValues  = array();

        foreach ($this->parameters as $key => $val) {
            $arg = $this->getArgName($key);
            $columnsNames[]    = $this->escape($key); 
            $columnsValues[]   = ':' . $arg;
        }        
        return trim(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->escape($this->tableName),
            implode(', ', $columnsNames),
            implode(', ', $columnsValues)
        ));
    }

    /**
     * Bind values parameters
     *
     * @access public
     * @return $this
     */
    public function bindValues()
    {
        foreach ($this->parameters as $key => $val) {
            $arg = $this->getArgName($key);
            $this->pdoParameters[$arg] = $val;
        }
        parent::bindValues();
    }   

    /**
     * Returns the ID of the last inserted row or sequence value 
     * This function returns false if there is no executed query.
     *
     * @access public
     * @return int|false     The last inserted id if found, otherwise false.
     */
    public function lastId()
    {
        return (empty(!$this->pdoStatement)) ? $this->driver->lastInsertedId() : false;
    }
}