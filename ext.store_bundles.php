<?php

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
		$this->EE = isset($settings['EE']) ? $settings['EE'] : ee();
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
	public function settings_form($current)
	{
		$this->EE->load->helper('form');
    	$this->EE->load->library('table');

    	$multis_field = isset($current['multis_field']) ? $current['multis_field'] : "";
    	$multis_channel_id = isset($current['multis_channel_id']) ? $current['multis_channel_id'] : "";
    	$discount_amt_field = isset($current['discount_amt_field']) ? $current['discount_amt_field'] : "";
    	$discounts_info_field = isset($current['discounts_info_field']) ? $current['discounts_info_field'] : "order_custom9";

    	$vars['settings'] = array(
			"multis_channel_id" => form_input('multis_channel_id', $multis_channel_id),
			"multis_field" => form_input('multis_field', $multis_field),
			"discount_amt_field" => form_input('discount_amt_field', $discount_amt_field),
			"discounts_info_field" => form_input('discounts_info_field', $discounts_info_field)
		);

    	return $this->EE->load->view('index', $vars, TRUE);
	}

	/**
	 * Save Settings
	 *
	 * This function provides a little extra processing and validation
	 * than the generic settings form.
	 *
	 * @return void
	 */
	function save_settings()
	{
	    if (empty($_POST))
	    {
	        show_error(lang('unauthorized_access'));
	    }

	    unset($_POST['submit']);

	    $this->EE->lang->loadfile('store_bundles');

	    $multis_field = ee()->input->post('multis_field');
	    $discount_amt_field = ee()->input->post('discount_amt_field');
	    $discounts_info_field = ee()->input->post('discounts_info_field');

	    if ( empty($multis_field) )
	    {
	        $this->EE->session->set_flashdata(
	                'message_failure',
	                sprintf(lang('multis_field'),
	                    $multis_field)
	        );
	        $this->EE->functions->redirect(
	            BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=store_bundles'
	        );
	    }

	    if ( empty($discount_amt_field) )
	    {
	        $this->EE->session->set_flashdata(
	                'message_failure',
	                sprintf(lang('discount_amt_field'),
	                    $discount_amt_field)
	        );
	        $this->EE->functions->redirect(
	            BASE.AMP.'C=addons_extensions'.AMP.'M=extension_settings'.AMP.'file=store_bundles'
	        );
	    }

	    $this->EE->db->where('class', __CLASS__);
	    $this->EE->db->update('extensions', array('settings' => serialize($_POST)));

	    $this->EE->session->set_flashdata(
	        'message_success',
	        lang('preferences_updated')
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

		$this->EE->db->insert('extensions', $data);	
		
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
		if(!$this->settings_exist(array(
			'multis_field',
			'multis_channel_id',
			'discount_amt_field',
			'discounts_info_field'
		))) {
			return false;
		}

		$discounts_info_field = $this->settings['discounts_info_field'];
		$order->$discounts_info_field = "";

		$multis = $this->get_bundle_entries();
		$items = $this->get_cart_items($order);

		if(count($items) < 1) {
			return false;
		}

		$item_ids = $this->get_item_ids($order->items);

		foreach($multis->result_array() as $multi)
		{
			$multi_entry = $this->get_multi_entry($multi);
			$multi_ids = $this->get_multi_ids($multi_entry);

			if($multi_ids) {
				$discounts_count = $this->detect_multi_discounts($item_ids, $multi_entry);
				$discount_amt = $this->calculate_discount_amt($discounts_count, $multi_entry);

				if($discount_amt > 0) {
					for($i = 0; $i < $discounts_count; $i++) {
						$order = $this->add_discounts_info($order, $multi_entry, $discount_amt);
					}
					$order->order_discount = $order->order_discount + $discount_amt;
					$order->order_total -= $discount_amt;
				}
			}
		}

        $order->save();

		return $order;
	}

	public function settings_exist($settings)
	{
		foreach($settings as $setting) {
			if(!isset($this->settings[$setting])) {
				return false;
			}
		}

		return true;
	}

	public function get_bundle_entries()
	{
		$this->EE->load->model('channel_entries_model');
		return $this->EE->channel_entries_model->get_entries(10);
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
			if(isset($item->entry_id)) {
				$ids[] = array("entry_id" => $item->entry_id, "item_qty" => $item->item_qty);
			}
		}
		return !empty($ids) ? $ids : null;
	}

	public function get_multi_ids($entry)
	{
		if( preg_match_all("/\\[([0-9]+)\\]/uim", $entry[$this->settings['multis_field']], $matches) )
		{
			return $matches[1];
		}
	}

	public function get_multi_discount_amt($entry)
	{
		return $entry[$this->settings['discount_amt_field']];
	}

	public function get_multi_entry($multi)
	{
		$this->EE->load->model('channel_entries_model');
		$entry = $this->EE->channel_entries_model->get_entry($multi['entry_id'], 10);
		$results = $entry->result_array();
		$entry = $results[0];
		return $entry;
	}

	public function detect_multi_discounts($item_ids, $multi_entry)
	{
		// Example: $item_ids = array(12, 34, 45, 56, 67); $multi_ids = array(12, 45, 67);
		$multi_ids = $this->get_multi_ids($multi_entry);
		$min = count($multi_ids);
		$matches = 0;

		while ($this->is_multi_discount($item_ids, $multi_ids, $min)) {
			$matches += 1;
		}
		return $matches;
	}

	public function is_multi_discount(&$item_ids, $multi_ids, $min)
	{
		$group = array();
		foreach($multi_ids as $index => $multi_item_id)
		{
			foreach($item_ids as $key => &$item)
			{
				if($item['entry_id'] == $multi_item_id && !in_array($multi_item_id, $group)) {
					$group[] = $multi_item_id;
					unset($multi_ids[$index]);
					$item['item_qty'] -= 1;
					if($item['item_qty'] < 1) {
						unset($item_ids[$key]);
					}
				}
			}
		}

		if(count($group) == $min) {
			return $group;
		}
	}

	public function calculate_discount_amt($discounts_count, $multi_entry)
	{
		$discount_amt = $this->get_multi_discount_amt($multi_entry);
		return $discount_amt * $discounts_count;
	}

	public function add_discounts_info($order, $multi_entry)
	{
		// $info = $this->update_discounts_info($order, $multi_entry, $discount_amt);

		$info_field = $this->settings['discounts_info_field'];
		$current_info = unserialize($order->$info_field);
		$discount_val = (float)$multi_entry[$this->settings['discount_amt_field']];

		if(empty($current_info)) {
			$current_info = array();
		}

		if(isset($current_info[$multi_entry['entry_id']])) {
			$discount = $current_info[$multi_entry['entry_id']];
			$discount['discount_val'] += $discount_val;
			$discount['discount'] = "&pound;" . number_format($discount['discount_val'], 2);
		}
		else {
			$discount = array(
				"title" => $multi_entry['title'],
				"discount_val" => $discount_val,
				"discount" => "&pound;" . number_format($discount_val, 2)
			);
		}
		
		$current_info[$multi_entry['entry_id']] = $discount;

		$order->$info_field = serialize($current_info);

		return $order;
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
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
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