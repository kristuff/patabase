<?php

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

use Kristuff\Patabase;
use Kristuff\Patabase\Driver;
use Kristuff\Patabase\Driver\DatabaseDriver;
use Kristuff\Patabase\Driver\ServerDriver;

/**
 * Class Datasource
 *
 * Abstract class for Database and Server class
 */
abstract class Datasource
{

    /**
     * @access protected
     * @var DatabaseDriver|ServerDriver   $driver     The Datasource driver
     */
    protected $driver = null;

    /**
     * @access protected
     * @var array               $settings   The driver settings
     */
    protected $settings= null;

    /**
     * Open a PDO connection
     *
     * @access protected
     * @return void
     */
    abstract protected function openConnection();

    /**
     * Get the current driver name
     *
     * @access public
     * @return string
     */
    public function getDriverName()
    {
        return $this->driver->getDriverName();    
    }

    /**
     * Initialize the database object by creating a driver
     *
     * @access public
     * @param array   $settings
     */
    public function __construct(array $settings = array())
    {
        $this->settings = $settings;        
        $this->openConnection();
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct()
    {
        $this->closeConnection();
    }

    /**
     * Has error
     *
     * @access public
     * @return bool     True if the last query has generated an error
     */
    public function hasError(): bool
    {
        return $this->driver->hasError();
    }

    /**
     * Get the last error code
     *
     * @access public
     * @return int    
     */
    public function errorCode(): int
    {
        return $this->driver->errorCode();
    }

    /**
     * Get the last error message
     *
     * @access public
     * @return string
     */
    public function errorMessage(): string
    {
        return $this->driver->errorMessage();
    }

    /**
     * Get the Driver
     *
     * @access public
     * @return 
     */
    public function getDriver()
    {
        return $this->driver;
    }
    
    /**
     * Get the PDO connection
     *
     * @access public
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        return $this->driver->getConnection();
    }

    /**
     * Close the PDO connection / reset driver
     *
     * @access public
     * @return void
     */
    public function closeConnection()
    {
        if ($this->driver){
            $this->driver->closeConnection();
            $this->driver = null;
        }
    }
}