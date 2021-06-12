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

namespace Kristuff\Patabase\Driver\Sqlite;

use Kristuff\Patabase;

/**
 * Class SqliteDatabase 
 */
class SqliteDatabase extends Patabase\Database
{
    /**
     * Creates and returns an instance of SqliteDatabase using given filepath
     *
     * @access public
     * @static
     * @param string   $filePath       The full path to database.
     *
     * @return SqliteDatabase
     */
    public static function createInstance(string $filePath): SqliteDatabase
    {
        $settings = array('driver' => 'sqlite', 'database'  => $filePath);
        return new SqliteDatabase($settings);
    }

    /**
     * Creates and returns an instance of 'in memory' SqliteDatabase 
     *
     * @access public
     * @static
     *
     * @return SqliteDatabase
     */
    public static function createMemoryInstance(): SqliteDatabase
    {
        $settings = array('driver' => 'sqlite', 'database'  => ':memory:');
        return new SqliteDatabase($settings);
    }

    /**
     * Get whether foreign keys are enabled or not
     *
     * https://www.sqlite.org/foreignkeys.html
     * Foreign key constraints are disabled by default (for backwards compatibility), so must be enabled separately for 
     * each database connection. (Note, however, that future releases of SQLite might change so that foreign key constraints 
     * enabled by default. Careful developers will not make any assumptions about whether or not foreign keys are enabled by 
     * default but will instead enable or disable them as necessary.) The application can also use a PRAGMA foreign_keys statement 
     * to determine if foreign keys are currently enabled.
     * 
     * @access public
     * @return bool     true if foreign keys are enabled, otherwise false
     */
    public function isForeignKeyEnabled(): bool
    {
        return $this->driver->isForeignKeyEnabled();
    }
}