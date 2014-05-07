<?php

namespace StoreBundles;

use Store\Model\OrderAdjustment;
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * Store Bundles Extension
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Extension
 * @author		John Clark
 * @link		
 */

class Store_bundles_ext {
	
	public $settings 		= array();
	public $description		= 'Store bundles';
	public $docs_url		= '';
	public $name			= 'Store Bundles';
	public $settings_exist	= 'y';
	public $version			= '0.1';
		
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->settings = $settings;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form
	 *
	 * If you wish for ExpressionEngine to automatically create your settings
	 * page, work in this method.  If you wish to have fine-grained control
	 * over your form, use the settings_form() and save_settings() methods 
	 * instead, and delete this one.
	 *
	 * @see http://expressionengine.com/user_guide/development/extensions.html#settings
	 */
	public function settings()
	{
		return array(
			
		);
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$this->settings = array();
		
		$data = array(
			'class'		=> __CLASS__,
			'method'	=> 'store_order_recalculate_end',
			'hook'		=> 'store_order_recalculate_end',
			'settings'	=> serialize($this->settings),
			'version'	=> $this->version,
			'enabled'	=> 'y'
		);

		ee()->db->insert('extensions', $data);	
		
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * store_order_adjusters
	 *
	 * @param 
	 * @return 
	 */
	public function store_order_recalculate_end($order)
	{
		$multis = $this->get_bundle_entries();
		$items = $this->get_cart_items($order);
		$item_ids = $this->get_item_ids($order->items);

		foreach($multis->result_array() as $multi)
		{
			$this->detect_multi_discount($item_ids, $multi);
		}

        $order->order_discount = 9;
        $order->save();

		return $order;
	}

	public function get_bundle_entries()
	{
		ee()->load->model('channel_entries_model');
		return ee()->channel_entries_model->get_entries(10);
	}

	public function get_cart_items($order)
	{
		return $order->items;
	}

	public function get_item_ids($items)
	{
		$ids = array();
		foreach($items as $item)
		{
			$ids[] = $item->entry_id;
		}
		return $ids;
	}

	public function get_multi_ids($entry)
	{
		$multis_field = "field_id_46";
		if( preg_match_all("/\\[([0-9]*)\\]/uim", $entry->$multis_field, $matches) )
		{
			return $matches[1];
		}
		else {
			return false;
		}
	}

	public function detect_multi_discount($item_ids, $multi)
	{
		ee()->load->model('channel_entries_model');
		$entry = ee()->channel_entries_model->get_entry($multi['entry_id'], 10);
		$multi_ids = $this->get_multi_ids($entry->row());
	}

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		ee()->db->where('class', __CLASS__);
		ee()->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.store_bundles.php */
/* Location: /system/expressionengine/third_party/store_bundles/ext.store_bundles.php */