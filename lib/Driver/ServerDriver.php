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
 * @version    0.3.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase\Driver;

use Kristuff\Patabase;
use Kristuff\Patabase\Driver\DatabaseDriver;
use Kristuff\Patabase\Exception;

/**
 *  Class ServerDriver
 *
 *  Base class for Database/Server drivers
 */
abstract class ServerDriver extends DatabaseDriver
{
    /**
     * Constructor
     *
     * @access public
     * @param  array    $settings               The connection settings
     * @param  bool     $isServerConnection     True for Server connection, default is false
     */
    public function __construct(array $settings, $isServerConnection = false)
    {
        // remove database attribute for server connection
        if ($isServerConnection){
            $dbKey = array_search('database', $this->dsnAttributes);
            if ($dbKey !== false) {
                unset($this->dsnAttributes[$dbKey]);
            }
        }
        parent::__construct($settings);
    }

    /**
     * Check if database exists
     *
     * @access public
     * @param  string   $databaseName   The database name
     *
     * @return bool     True if the given database exists, otherwise false.
     */
    abstract public function databaseExists($databaseName);

    /**
     * Create a database
     *
     * @access public
     * @param  string   $databaseName   The database name.
     * @param  string   $owner          The database owner. This parameter is honored in pgsql only.
     *
     * @return bool     True if the database has been created, otherwise false.
     */
    abstract public function createDatabase($databaseName, $owner);

    /**
     * Create a user
     *
     * @access public
     * @param  string   $userName       The user name
     * @param  string   $userpassword   The user password
     *
     * @return bool     True if the user has been created, otherwise false. 
     */
    abstract public function createUser($userName, $userPassword);

    /**
     * Drop a user
     *
     * @access public
     * @param  string   $userName       The user name
     * @param  bool     $ifExists       (optional) True if the user must be deleted only when exists. Default is false.
     *
     * @return bool     True if the user has been dropped or does not exist when $ifExists is set to True, otherwise false. 
     */
    abstract public function dropUser($userName, $ifExists = false);
    
    /**
     * Grant user permissions on given database
     *
     * @access public
     * @param  string   $databaseName   The database name
     * @param  string   $userName       The user name
     *
     * @return bool     True if the user has been granted, otherwise false. 
     */
    abstract public function grantUser($databaseName, $userName);

    /**
     * Get the SQL for show databases
     *
     * @access public
     * @return string
     */
    abstract public function sqlShowDatabases();

    /**
     * Get the SQL for show users
     *
     * @access public
     * @return string
     */
    abstract public function sqlShowUsers();
}