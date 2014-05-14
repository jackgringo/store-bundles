<?php

require('../ext.store_bundles.php');
require('./stubs/ee.php');

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-05-07 at 10:11:48.
 */
class Store_bundles_extTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Store_bundles_ext
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Store_bundles_ext(array(
            "EE" => new MockEE(),
            "multis_channel_id" => 10,
            "multis_field" => "field_id_46",
            "discount_amt_field" => "field_id_47",
            "discounts_info_field" => "order_custom9"
        ));
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Store_bundles_ext::store_order_recalculate_end
     * @todo   Implement testStore_order_recalculate_end().
     * @group integration
     */
    public function testStore_order_recalculate_end()
    {
        $order = new MockOrder(array(
            new MockItem(12, 2),
            new MockItem(12, 2)
        ));
        $final = $this->object->store_order_recalculate_end($order);
        $this->assertEquals(0, $final->order_discount);

        $order = new MockOrder(array(
            new MockItem(12),
            new MockItem(45),
            new MockItem(56)
        ));
        $final = $this->object->store_order_recalculate_end($order);
        $this->assertEquals(3.5, $final->order_discount);
        $this->assertEquals(-3.5, $final->order_total);

        $order = new MockOrder(array(
            new MockItem(12, 2),
            new MockItem(45, 2),
            new MockItem(56, 2)
        ));
        $final = $this->object->store_order_recalculate_end($order);
        $this->assertEquals(7, $final->order_discount);
        $this->assertEquals(-7, $final->order_total);

        $order = new MockOrder(array(
            new MockItem(12, 2),
            new MockItem(45, 2),
            new MockItem(56)
        ));
        $final = $this->object->store_order_recalculate_end($order);
        $this->assertEquals(3.5, $final->order_discount);
        $this->assertEquals(-3.5, $final->order_total);

        $order = new MockOrder(array(
            new MockItem(12),
            new MockItem(45),
            new MockItem(56)
        ));
        $order->order_discount = 20;
        $order->order_total = 120;
        $final = $this->object->store_order_recalculate_end($order);
        $this->assertEquals(23.5, $final->order_discount);
        $this->assertEquals(116.5, $final->order_total);


        $order = new MockOrder(array(
            new MockItem(12),
            new MockItem(45),
            new MockItem(56)
        ));
        $order->order_custom9 = 'a:3:{i:0;a:2:{s:5:"title";s:12:"Crime Series";s:8:"discount";d:3.5;}i:1;a:2:{s:5:"title";s:12:"Crime Series";s:8:"discount";s:6:"3.5000";}i:2;a:3:{s:5:"title";s:12:"Crime Series";s:12:"discount_val";d:3.5;s:8:"discount";s:11:"&pound;3.50";}}';

        $final = $this->object->store_order_recalculate_end($order);
        $this->assertCount(1, unserialize($final->order_custom9));
    }

    /**
     * @covers Store_bundles_ext::get_bundle_entries
     * @todo   Implement testGet_bundle_entries().
     */
    public function testGet_bundle_entries()
    {
        $this->assertInstanceOf('MockChannelEntriesResult', $this->object->get_bundle_entries());
    }

    public function testGet_multi_entry()
    {
        $entry = $this->object->get_multi_entry(array("entry_id" => "1234"), 10);
        // $this->assertEquals(1234, $entry['entry_id']);
    }

    /**
     * @covers Store_bundles_ext::get_cart_items
     * @todo   Implement testGet_cart_items().
     */
    public function testGet_cart_items()
    {
        $order = new stdClass();
        $order->items = new stdClass();

        $items = $this->object->get_cart_items($order);
        $this->assertFalse(empty($items));

        $order->items = null;
        $items = $this->object->get_cart_items($order);
        $this->assertNull($items);
    }

    /**
     * @covers Store_bundles_ext::get_item_ids
     * @todo   Implement testGet_item_ids().
     */
    public function testGet_item_ids()
    {
        $entry = new MockItem(23);
        $entry2 = new MockItem(46, 2);
        $entries = array($entry, $entry2);
        $this->assertEquals(array(
            array("entry_id" => 23, "item_qty" => 1),
            array("entry_id" => 46, "item_qty" => 2)
        ), $this->object->get_item_ids($entries));

        $entry3 = new MockItem(null);
        $entries[] = $entry3;
        $this->assertEquals(array(
            array("entry_id" => 23, "item_qty" => 1),
            array("entry_id" => 46, "item_qty" => 2)
        ), $this->object->get_item_ids($entries));

        $empty = array();
        $this->assertNull($this->object->get_item_ids($empty));
    }

    /**
     * @covers Store_bundles_ext::get_multi_ids
     * @todo   Implement testGet_multi_ids().
     */
    public function testGet_multi_ids()
    {
        $entry = array("field_id_46" => "[23] [34][45]");
        $this->assertCount(3, $this->object->get_multi_ids($entry));

        $entry = array("field_id_46" => "23 45 67");
        $this->assertNull($this->object->get_multi_ids($entry));

        $entry = array("field_id_46" => "[]23 [23 45] 123");
        $this->assertNull($this->object->get_multi_ids($entry));
    }

    /**
     * @covers Store_bundles_ext::is_multi_discount
     * @todo   Implement testIs_multi_discount().
     */
    public function testIs_multi_discount()
    {
        $item_ids = array(
            array("entry_id" => 12, "item_qty" => 1),
            array("entry_id" => 23, "item_qty" => 1),
            array("entry_id" => 34, "item_qty" => 1),
            array("entry_id" => 45, "item_qty" => 1),
            array("entry_id" => 67, "item_qty" => 1)
        );
        $multi_ids = array(12, 45, 67);

        $group = $this->object->is_multi_discount($item_ids, $multi_ids, 3);
        $this->assertCount(3, $group);

        $item_ids = array(
            array("entry_id" => 12, "item_qty" => 1),
            array("entry_id" => 23, "item_qty" => 1),
            array("entry_id" => 34, "item_qty" => 1),
            array("entry_id" => 45, "item_qty" => 1),
            array("entry_id" => 67, "item_qty" => 1)
        );
        $multi_ids = array(12, 45, 78);

        $group = $this->object->is_multi_discount($item_ids, $multi_ids, 3);
        $this->assertNull($group);
    }

    /**
     * @covers Store_bundles_ext::detect_multi_discount
     * @todo   Implement testDetect_multi_discount().
     */
    public function testDetect_multi_discounts()
    {
        $item_ids = array(
            array("entry_id" => 12, "item_qty" => 1),
            array("entry_id" => 23, "item_qty" => 1),
            array("entry_id" => 34, "item_qty" => 1),
            array("entry_id" => 45, "item_qty" => 1),
            array("entry_id" => 56, "item_qty" => 1)
        );
        $multi_ids = array("field_id_46" => "[12][45][56]");

        $matches = $this->object->detect_multi_discounts($item_ids, $multi_ids);
        $this->assertEquals(1, $matches);

        $item_ids = array(
            array("entry_id" => 12, "item_qty" => 2),
            array("entry_id" => 23, "item_qty" => 1),
            array("entry_id" => 34, "item_qty" => 1),
            array("entry_id" => 45, "item_qty" => 2),
            array("entry_id" => 56, "item_qty" => 2)
        );
        $multi_ids = array("field_id_46" => "[12][45][56]");

        $matches = $this->object->detect_multi_discounts($item_ids, $multi_ids);
        $this->assertEquals(2, $matches);

        $item_ids = array(
            array("entry_id" => 12, "item_qty" => 1),
            array("entry_id" => 23, "item_qty" => 1),
            array("entry_id" => 34, "item_qty" => 1),
            array("entry_id" => 45, "item_qty" => 1),
            array("entry_id" => 56, "item_qty" => 1)
        );
        $multi_ids = array("field_id_46" => "[122][435][546]");

        $matches = $this->object->detect_multi_discounts($item_ids, $multi_ids);
        $this->assertEquals(0, $matches);
    }

    /**
     * @covers Store_bundles_ext::detect_multi_discount
     * @todo   Implement testDetect_multi_discount().
     */
    public function testCalculate_discount_amt()
    {
        $multi_entry = array("field_id_47" => "3.5000");
        $discounts_count = 2;
        $this->assertEquals(7, $this->object->calculate_discount_amt($discounts_count, $multi_entry));

        $discounts_count = 10;
        $this->assertEquals(35, $this->object->calculate_discount_amt($discounts_count, $multi_entry));

        $multi_entry = array("field_id_47" => "");
        $discounts_count = 2;
        $this->assertEquals(0, $this->object->calculate_discount_amt($discounts_count, $multi_entry));

        $multi_entry = array("field_id_47" => null);
        $discounts_count = null;
        $this->assertEquals(0, $this->object->calculate_discount_amt($discounts_count, $multi_entry));
    }

    public function testGet_multi_discount_amt()
    {
        $multi_entry = array("field_id_47" => "3.5000");
        $this->assertEquals(3.5, $this->object->get_multi_discount_amt($multi_entry));
    }

    /**
     * @covers Store_bundles_ext::add_discounts_info
     * @group text
     */
    public function testAdd_discounts_info()
    {
        $order = new MockOrder(array(
            new MockItem(12),
            new MockItem(45),
            new MockItem(56)
        ));
        $discount_entry = array("entry_id" => 1234, "title" => "Multi Superb", "field_id_46" => "[12][45][56]", "field_id_47" => "3.500" );
        $order = $this->object->add_discounts_info($order, $discount_entry);

        $this->assertEquals(
            array("1234" => array(
                "title" => "Multi Superb",
                "discount_val" => "3.5",
                "discount" => "&pound;" . number_format(3.5, 2))
            ),
            unserialize($order->order_custom9)
        );
    }
}
