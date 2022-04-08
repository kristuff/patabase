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
 * @version    1.0.1
 * @copyright  2017-2022 Christophe Buliard
 */

namespace Kristuff\Patabase\Driver;

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
     * @param array    $settings               The connection settings
     * @param bool     $isServerConnection     True for Server connection, default is false
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