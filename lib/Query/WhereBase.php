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

namespace Kristuff\Patabase\Query;

use Kristuff\Patabase;
use Kristuff\Patabase\Query\QueryBuilder;
use Kristuff\Patabase\Query\QueryFilter;
use Kristuff\Patabase\Query\Select;
use Kristuff\Patabase\Driver\DatabaseDriver;

/**
 * Class WhereBase
 *
 * Abstract base class for Where
 */
abstract class WhereBase extends QueryFilter
{
    /**
     * sql base: WHERE or HAVING
     *
     * @access protected
     * @var    string
     */
    protected $sqlBase = 'WHERE';   
}