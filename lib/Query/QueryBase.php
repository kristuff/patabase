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

namespace Kristuff\Patabase\Query;

/**
 * Class QueryBuilder
 *
 * Abstract base class for queries
 */
abstract class QueryBase
{
    /**
     * Error
     *
     * @access protected
     * @var    array                $error
     */
    protected $error = array();

    /**
     * Has error
     *
     * @access public
     * @return bool             True if the last query execution has genaretd an error
     */
    public function hasError(): bool
    {
        return !empty($this->error);
    }

    /**
     * Error code
     *
     * @access public
     * @return mixed|null      The last error code reported if any, otherwise null. 
     */
    public function errorCode()
    {
        return !empty($this->error) ? $this->error['code']: null;
    }

    /**
     * Error message
     *
     * @access public
     * @return string|null     The last error message reported if any, otherwise null. 
     */
    public function errorMessage()
    {
        return isset($this->error['message']) ? $this->error['message'] : null;
    }
 
}