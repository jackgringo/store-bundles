<?php

require('../mod.store_bundles.php');
require('./mocks/mocks.php');
/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-05-08 at 14:39:11.
 */
class Store_bundlesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Store_bundles
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Store_bundles(new MockEE);
        $this->object->settings = array(
            "discounts_info_field" => "order_custom9"
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Store_bundles::get_info_field
     * @todo   Implement testGet_info_field().
     */
    public function testGet_info_field()
    {
        $this->assertEquals("order_custom9", $this->object->get_info_field());
    }
}
