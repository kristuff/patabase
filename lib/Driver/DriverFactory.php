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

namespace Kristuff\Patabase\Driver;

use Kristuff\Patabase;
use Kristuff\Patabase\Driver;
use Kristuff\Patabase\Exception;

/**
 * Class DriverFactory
 */
class DriverFactory
{
    /**
     * Get Server/Database driver from given settings
     *
     * @access public
     * @param  array    $settings               The connection settings
     * @param  bool     $isServerConnection     True for Server connection, default is false
     *
     * @return MysqlDriver|PostgresDriver|SqliteDriver
     * @throw  Exception\InvalidArgException 
     */
    public static function getInstance(array $settings, $isServerConnection = false)
    {
        if (! isset($settings['driver'])) {
            throw new Exception\MissingArgException('You must define a driver');
        }

        // get driver
        switch ($settings['driver']) {
            case 'sqlite':
                return new Driver\Sqlite\SqliteDriver($settings);
            case 'mysql':
                return new Driver\Mysql\MysqlDriver($settings, $isServerConnection);
            case 'pgsql':
                return new Driver\Postgres\PostgresDriver($settings, $isServerConnection);
            default:
                throw new Exception\InvalidArgException('The specified driver is not supported');
        }
    }  
}