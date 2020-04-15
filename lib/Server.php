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
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase;

use Kristuff\Patabase;
use Kristuff\Patabase\Driver;
use Kristuff\Patabase\Query;
use Kristuff\Patabase\Datasource;

/**
 * Class Server
 *
 * Represents a connection to SQL database server
 */
 class Server extends Datasource
{

    /**
     * Open a server connection
     *
     * @access protected
     * @return void
     */
    protected function openConnection()
    {
        $this->driver = Driver\DriverFactory::getInstance($this->settings, true);
    }

    /**
     * Get a list of active databases in the server
     *
     * @access public
     * @return array    A non indexed array containing databases names
     */
    public function getDatabases()
    {
        $sql = $this->driver->sqlShowDatabases();
        $query = $this->driver->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get a list of active users in the server
     *
     * @access public
     * @return array    A non indexed array containing users names
     */
    public function getUsers()
    {
        $sql = $this->driver->sqlShowUsers();
        $query = $this->driver->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Get whether the given database exists in the server
     *
     * @access public
     * @param  string   $databaseName           The database name
     *
     * @return bool     True if the given database exists, otherwise false.
     */
    public function databaseExists($databaseName)
    {
        return $this->driver->databaseExists($databaseName);
    }

    /**
     * Get whether the given user exists in the server
     *
     * @access public
     * @param  string   $userName               The user name
     *
     * @return bool     True if the given user exists, otherwise false.
     */
    public function userExists($userName)
    {
        $usrs = $this->getUsers();
        return in_array($userName, $usrs);
    }

    /**
     * Create a database and a user for this database
     *
     * @access public
     * @param  string   $databaseName           The database name
     * @param  string   $userName               The database user name
     * @param  string   $password               The user password
     *
     * @return bool     True if the database and user have been created, otherwise false.
     */ 
    public function createDatabaseAndUser($databaseName, $userName, $password)
    {
        return $this->createUser($userName, $password)
                && $this->createDatabase($databaseName)
                && $this->grantUser($databaseName, $userName);
    }

    /**
     * Create a database
     *
     * @access public
     * @param  string   $databaseName           The database name
     * @param  string   $owner (optional)       The database owner (used in postgres only). Default is null.
     *
     * @return bool     True if the database has been created, otherwise false.
     */ 
    public function createDatabase($databaseName, $owner = null)
    {
        return $this->driver->createDatabase($databaseName, $owner);
    }

    /**
     * Drop a database
     *
     * @access public
     * @param  string   $databaseName           The database name.
     * @param  bool     $ifExists (optional)    Set whether the database must be deleted only when exists.
     *
     * @return bool     True if the database has been dropped or does not exist when $ifExists 
     *                  is set to True, otherwise false. 
     */ 
    public function dropDatabase($databaseName, $ifExists = false)
    {
        $sql = trim(sprintf('DROP DATABASE %s %s', 
            $ifExists === true ? 'IF EXISTS': '',
            $this->driver->escape($databaseName)));
        return $this->driver->prepareAndExecuteSql($sql);
    }
   
    /**
     * Create a user
     *
     * @access public
     * @param  string   $userName               The user name
     * @param  string   $userpassword           The user password
     *
     * @return bool     True if the user has been created, otherwise false
     */
    public function createUser($userName, $userPassword)
    {
        return $this->driver->createUser($userName, $userPassword);
    }

    /**
     * Grant user permissions on given database
     *
     * @access public
     * @param  string   $databaseName           The database name
     * @param  string   $userName               The user name
     * @return bool     True if the user has been granted, otherwise false
     */
    public function grantUser($databaseName, $userName)
    {
        return $this->driver->grantUser($databaseName, $userName);
    }

    /**
     * Drop a user
     *
     * @access public
     * @param  string   $userName               The user name
     * @param  bool     $ifExists               Set whether the user must be deleted only when exists.
     *
     * @return bool     True if the user has been dropped or does not exist when $ifExists 
     *                  is set to True, otherwise false. 
     */
    public function dropUser($userName, $ifExists = false)
    {
        return $this->driver->dropUser($userName, $ifExists);
    }
}