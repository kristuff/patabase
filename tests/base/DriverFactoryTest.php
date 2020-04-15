<?php

require_once __DIR__.'/../../vendor/autoload.php';
use PHPUnit\Framework\TestCase;

class DriverFactoryTest extends TestCase
{

    /**
     * @expectedException Kristuff\Patabase\Exception\MissingArgException
     */
    public function testDatabaseMissingRequiredParameter()
    {
        new Kristuff\Patabase\Database(array());
    }

    /**
     * @expectedException Kristuff\Patabase\Exception\InvalidArgException
     */
    public function testDatabaseInvalidRequiredParameter()
    {
        new Kristuff\Patabase\Database(array('driver' => 'XX'));
    }

    /**
     * @expectedException Kristuff\Patabase\Exception\MissingArgException
     */
    public function testServerMissingRequiredParameter()
    {
        new Kristuff\Patabase\Server(array());
    }

    /**
     * @expectedException Kristuff\Patabase\Exception\InvalidArgException
     */
    public function testServerInvalidRequiredParameter()
    {
        new Kristuff\Patabase\Server(array('driver' => 'XX'));
    }

}
