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

namespace Kristuff\Patabase;

use Kristuff\Patabase\Datasource;
use Kristuff\Patabase\Driver;
use Kristuff\Patabase\Driver\ServerDriver;

/**
 * Class Server
 *
 * Represents a connection to SQL database server
 */
 class Server extends Datasource
{
    /**
     * @access protected
     * @var ServerDriver        $driver     The Datasource driver
     */
    protected $driver = null;

    /**
     * Open a server connection
     *
     * @access protected
     * @return void
     */
    protected function openConnection(): void
    {
        $this->driver = Driver\DriverFactory::getInstance($this->settings, true);
    }

    /**
     * Get a list of active databases in the server
     *
     * @access public
     * @return array    A non indexed array containing databases names
     */
    public function getDatabases(): array
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
    public function getUsers(): array
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
     * @param string   $databaseName           The database name
     *
     * @return bool     True if the given database exists, otherwise false.
     */
    public function databaseExists($databaseName): bool
    {
        return $this->driver->databaseExists($databaseName);
    }

    /**
     * Get whether the given user exists in the server
     *
     * @access public
     * @param string   $userName               The user name
     *
     * @return bool     True if the given user exists, otherwise false.
     */
    public function userExists($userName): bool
    {
        $usrs = $this->getUsers();
        return in_array($userName, $usrs);
    }

    /**
     * Create a database and a user for this database
     *
     * @access public
     * @param string   $databaseName           The database name
     * @param string   $userName               The database user name
     * @param string   $password               The user password
     *
     * @return bool     True if the database and user have been created, otherwise false.
     */ 
    public function createDatabaseAndUser(string $databaseName, string $userName, string $password): bool
    {
        return $this->createUser($userName, $password)
                && $this->createDatabase($databaseName)
                && $this->grantUser($databaseName, $userName);
    }

    /**
     * Create a database
     *
     * @access public
     * @param string   $databaseName           The database name
     * @param string   $owner (optional)       The database owner (used in postgres only). Default is null.
     *
     * @return bool     True if the database has been created, otherwise false.
     */ 
    public function createDatabase(string $databaseName, ?string $owner = null): bool
    {
        return $this->driver->createDatabase($databaseName, $owner);
    }

    /**
     * Drop a database
     *
     * @access public
     * @param string   $databaseName           The database name.
     * @param bool     $ifExists (optional)    Set whether the database must be deleted only when exists.
     *
     * @return bool     True if the database has been dropped or does not exist when $ifExists 
     *                  is set to True, otherwise false. 
     */ 
    public function dropDatabase(string $databaseName, bool $ifExists = false): bool
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
     * @param string   $userName               The user name
     * @param string   $userpassword           The user password
     *
     * @return bool     True if the user has been created, otherwise false
     */
    public function createUser(string $userName, string $userPassword): bool
    {
        return $this->driver->createUser($userName, $userPassword);
    }

    /**
     * Grant user permissions on given database
     *
     * @access public
     * @param string   $databaseName           The database name
     * @param string   $userName               The user name
     * 
     * @return bool     True if the user has been granted, otherwise false
     */
    public function grantUser(string $databaseName, string $userName): bool
    {
        return $this->driver->grantUser($databaseName, $userName);
    }

    /**
     * Drop a user
     *
     * @access public
     * @param string   $userName               The user name
     * @param bool     $ifExists               Set whether the user must be deleted only when exists.
     *
     * @return bool     True if the user has been dropped or does not exist when $ifExists 
     *                  is set to True, otherwise false. 
     */
    public function dropUser(string $userName, bool $ifExists = false): bool
    {
        return $this->driver->dropUser($userName, $ifExists);
    }
}