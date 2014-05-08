<?php

/**
 * Store Bundles Module Front End File
 *
 * @category	Module
 * @author		John Clark
 */

class Store_bundles {

	public $return_data;

	/**
	 * Constructor
	 */
	public function __construct($ee = null)
	{
		$this->EE = isset($ee) ? $ee : ee();
		if( ($this->settings = $this->get_ext_settings()) === FALSE ) {
			return false;
		}
	}

	public function discounts()
	{
		$order = $this->EE->store->orders->get_cart();
		$discount_info_field = $this->get_info_field();
		$discounts = unserialize($order->$discount_info_field);
		foreach($discounts as &$discount) {
			$discount['discount_title'] = $discount['title'];
			unset($discount['title']);
		}
		if(!empty($discounts)){
			return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $discounts);
		}
	}

	public function get_info_field()
	{
		return $this->settings['discounts_info_field'];
	}

	public function get_ext_settings()
	{
		$query = $this->EE->db->limit(1)->get_where("extensions", array("class" => "Store_bundles_ext"));
		if($query->num_rows() > 0) {
			$settings = $query->row('settings');
			return unserialize($settings);
		}
	}

}

/* End of file mod.store_bundles.php */
/* Location: /system/expressionengine/third_party/store_bundles/mod.store_bundles.php */
