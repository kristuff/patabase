<?php

require_once __DIR__.'/../../vendor/autoload.php';

use Kristuff\Patabase;
use Kristuff\Patabase\Database;
use Kristuff\Patabase\Table;
use Kristuff\Patabase\SqlException;
use Kristuff\Patabase\Query\CreateTable;
use Kristuff\Patabase\Query\Update;
use PHPUnit\Framework\TestCase;


abstract class DatabaseInjectionTest extends TestCase
{
    /**
     * @var Kristuff\Patabase\Database
     */
    protected static $db;



   
}