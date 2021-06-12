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

namespace Kristuff\Patabase\Driver\Postgres;

use Kristuff\Patabase\Driver\ServerDriver;

/**
 * Class Driver
 *
 * Postgres Sql Driver
 * 
 * Data types (main):
 *  [Numeric Types]
 *      smallint, integer, bigint, (serial/bigserial)
 *      decimal, numeric 	
 *      real, double
 *  [Character Types] 
 *      character varying(n), varchar(n) 
 *      character(n), char(n) 
 *      text
 *  [Binary Data Types]
 *      bytea
 *  [Date/Time Types]
 *      timestamp, time, date, interval
 *  [Boolean Type]
 *      boolean     (literal: TRUE 't' 'true' 'y' 'yes' 'on' '1' | FALSE 'f' 'false' 'n' 'no' 'off' '0')
 *  [Geometric Types] 
 *  [Network Address Types]
 *  [Bit String Types]
 *      bit(n), bit varying(n)  cast...
 *  [UUID Type]
 *      uuid        
 */
class PostgresDriver extends ServerDriver
{
    /**
     * List of DSN attributes
     *
     * @access protected
     * @var array
     */
    protected $dsnAttributes = array(
        'hostname',
        'username',
        'password',
        'database'
    );

    /**
     * Escape an identifier
     *
     * @access public
     * @param string  $identifier
     *
     * @return string
     */
    public function escapeIdentifier(string $identifier) : string
    {
        return '"'.$identifier.'"';
    }
     
    /**
     * Escape a value
     *
     * @access public
     * @param string  $value
     *
     * @return string
     */
    public function escapeValue(string $value): string
    {
        return "'".$value."'";
    }

    /**
     * Create a new PDO connection
     *
     * @access public
     * @param array   $settings
     *
     * @return void
     */
    public function createConnection(array $settings): void
    {
        $port    = !empty($settings['port'])     ?  ';port='.$settings['port']        : '';
        $dbname  = !empty($settings['database']) ?  ';dbname='.$settings['database']  : '';
        $dsn     = 'pgsql:host='.$settings['hostname'] .$port .$dbname ;

        $this->pdo = new \PDO(
            $dsn,
            $settings['username'],
            $settings['password'],
            array()
        );

        // make sure emulate prepare is false 
        //$this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get last inserted id
     *
     * @access public
     * @return string
     */
    public function lastInsertedId(): string
    {
       // Postgres does not set pdo->lastInsertedId
       // use sequence
       try {
            $rq = $this->pdo->prepare('SELECT LASTVAL()');
            $rq->execute();
            // return string 
            return strval($rq->fetchColumn());
        }
        catch (\PDOException $e) {
            return 0;
        }
    }

    /**
     * Enable foreign keys
     *
     * @access public
     * @return void
     */
    public function enableForeignKeys(): void
    {
    }

    /**
     * Disable foreign keys
     *
     * @access public
     * @return void
     */
    public function disableForeignKeys(): void
    {
    }
    
    /**
     * Get whether foreign keys are enabled or not
     * For compatibility with Sqlite, not implemented in that driver, return false 

     * @access public
     * @return bool     true if foreign keys are enabled, otherwise false
     */
    public function isForeignKeyEnabled(): bool
    {
        return false;
    }

    /**
     * Add a foreign key
     * 
     * @access public
     * @param string   $fkName         The constraint name
     * @param string   $srcTable       The source table
     * @param string   $srcColumn      The source column 
     * @param string   $refTable       The referenced table
     * @param string   $refColumn      The referenced column
     *
     * @return bool    True if the foreign key has been created, otherwise false
     */
    public function addForeignKey(string $fkName, string $srcTable, string $srcColumn, string $refTable, string $refColumn): bool
    {
        $sql = sprintf('ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s(%s)',
                       $this->escape($srcTable),
                       $fkName,
                       $this->escape($srcColumn),
                       $this->escape($refTable),
                       $this->escape($refColumn)
        );
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Drop a foreign key
     * 
     * @access public
     * @param string   $fkName         The constraint name
     * @param string   $tableName      The source table
     *
     * @return bool    True if the foreign key has been dropped, otherwise false
     */
    public function dropForeignKey(string $fkName, string $tableName, bool $ifExists = false): bool
    {
        $sql = sprintf('ALTER TABLE %s DROP CONSTRAINT %s %s',
                       $this->escape($tableName),
                       $ifExists ? 'IF EXISTS' : '',
                       $fkName
        );
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Checks if a database exists
     *
     * @access public
     * @param string   $databaseName   The database name
     *
     * @return bool     True if the given database exists, otherwise false.
     */
    public function databaseExists(string $databaseName): bool
    {
        $sql = 'SELECT COUNT(*) FROM pg_database WHERE datname = :dbName'; 
        $query = $this->pdo->prepare($sql);
        $query->bindValue(':dbName',  $databaseName, \PDO::PARAM_STR);
        $query->execute();
        return (bool) $query->fetchColumn();
    }

    /**
     * Create a database
     *
     * @access public
     * @param string   $databaseName   The database name
     * @param string   $owner          The database owner. 
     * @param string   $template       The template to use. Default is 'template0'
     *
     * @return bool     True if the database has been created, otherwise false.
     */
    public function createDatabase(string $databaseName, ?string $owner= null, ?string $template = 'template0'): bool
    {
        $sql = trim(sprintf('CREATE DATABASE %s %s TEMPLATE %s', 
            $this->escape($databaseName),
            isset($owner) ? 'OWNER '. $this->escape($owner) : '', 
            $template
        ));
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Create a user
     *
     * @access public
     * @param string   $userName       The user name
     * @param string   $userpassword   The user password
     *
     * @return bool     True if the user has been created, otherwise false. 
     */
    public function createUser(string $userName, string $userPassword): bool
    {
        $sql = trim(sprintf('CREATE USER %s PASSWORD %s', 
                    $this->escape($userName), 
                    "'" . $userPassword ."'"
        ));
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Drop a user
     *
     * @access public
     * @param string   $userName       The user name
     * @param bool     $ifExists       (optional) True if the user must be deleted only when exists. Default is false.
     *
     * @return bool     True if the user has been dropped or does not exist when $ifExists is set to True, otherwise false. 
     */
    public function dropUser(string $userName, bool $ifExists = false): bool
    {
        $sql = trim(sprintf('DROP USER %s %s', 
                    $ifExists === true ? 'IF EXISTS': '',
                    $this->escape($userName)
        ));
        return $this->prepareAndExecuteSql($sql);
    }
    
    /**
     * Grant user permissions on given database
     *
     * @access public
     * @param string   $databaseName   The database name
     * @param string   $userName       The user name
     *
     * @return bool     True if the user has been granted, otherwise false. 
     */
    public function grantUser(string $databaseName, string $userName): bool
    {
        // ALL PRIVILEGES Grant all of the available privileges at once. The PRIVILEGES keyword 
        // is optional in PostgreSQL, though it is required by strict SQL.

        // GRANT CONNECT ON DATABASE database_name TO user_name;
        //$sql = trim(sprintf('GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA %s TO %s;', 
        $sql = trim(sprintf('GRANT CONNECT ON DATABASE %s TO %s;', 
            $this->escape($databaseName),
            $this->escape($userName)
        ));
        return $this->prepareAndExecuteSql($sql);
    }

    /**
     * Get the SQL for show databases
     *
     * @access public
     * @return string
     */
    public function sqlShowDatabases(): string
    {
        return 'SELECT datname FROM pg_database WHERE datistemplate = false;';
    }

    /**
     * Get the SQL for show tables
     *
     * @access public
     * @return string
     */
    public function sqlShowTables(): string
    {
        return "SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type = 'BASE TABLE' ORDER BY table_name;";
    }

    /**
     * Get the SQL for show users
     *
     * @access public
     * @return string
     */
    public function sqlShowUsers(): string
    {
        return 'SELECT usename FROM pg_user';
    }

    /**
     * Get the SQL for random function 
     *
     * Parameter $seed is not honored in Postgres.
     *
     * @access public
     * @param  int   $seed    The random seed. Default is null. 
     *
     * @return string         
     */
    public function sqlRandom($seed = null): string
    {
        return 'random()';   
    }

    /**
     * Get the SQL for auto increment column
     *
     * @access public
     * @param string   $type   The sql column type
     * 
     * @return string
     */
    public function sqlColumnAutoIncrement(string $type): string
    {
        // SERIAL/BIGSERIAL is a type in postgres
        return strtolower($type) === 'bigint' ? 'bigserial' : 'serial';
    }



}