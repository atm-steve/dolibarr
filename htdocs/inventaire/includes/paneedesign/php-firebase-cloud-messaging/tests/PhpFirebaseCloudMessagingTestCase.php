<?php
namespace PaneeDesign\PhpFirebaseCloudMessaging\Tests;

/**
 * @author Fabiano Roberto <fabiano@paneedesign.com>
 */
class PhpFirebaseCloudMessagingTestCase extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }
}