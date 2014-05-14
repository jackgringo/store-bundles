store-bundles
=============

Beginnings of an [ExpressionEngine](http://ellislab.com/expressionengine) add-on to bring product bundles/packages to [Expresso Store](https://exp-resso.com/). Currently as rough as a badger's back end. Pull requests all welcome!

Requires [Playa](http://devot-ee.com/add-ons/playa) for managing bundles.

To set up:

- Create channel for bundles with Playa field containing products, and text field (**number**) for the bundle discount e.g. 3.50
- In extension settings, set channel and field IDs (in field_id_* format)

To display bundle discounts in checkout/order templates, use the {exp:store_bundles:discount} tag pair:

    {exp:store_bundles:discounts}
        {discount_title}	// title of bundle entry e.g. "Multi-event discount"
        {discount}			// e.g. £3.50
        {discount_val}		// e.g. 3.50
    {/exp:store_bundles:discounts}