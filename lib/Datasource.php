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
use Kristuff\Patabase\Driver\DatabaseDriver;

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
     * Get Patabase Version
     *
     * @access public
     * @method static
     * @return string  format (0.0.0)
     */
    public static function getVersion()
    {
        return DatabaseDriver::getVersion();
    }

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
     * @param  array   $settings
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
    public function hasError()
    {
        return $this->driver->hasError();
    }

    /**
     * Get the last error code
     *
     * @access public
     * @return int    
     */
    public function errorCode()
    {
        return $this->driver->errorCode();
    }

    /**
     * Get the last error message
     *
     * @access public
     * @return string
     */
    public function errorMessage()
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
     * @return PDO
     */
    public function getConnection()
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