<?php
/**
 * Project: ecosupplies
 * File: MapOptions.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/2/2015
 * Time: 10:03 πμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace BestPrice\Panels;


use XDaRk_v141110\Panels\Panel;

class MapOptions extends Panel {
	protected $tab = 0;
	protected $type = 'main';
	protected $title = 'BestPrice.gr Map Options';
	protected $image = false; // TODO set a default image
	protected $input = array();
	protected $submit = array(
		'title' => 'Save',
		'class' => 'button pull-right'
	);

	public function __construct( $moduleInstance ) {
		parent::__construct( $moduleInstance );

		$productIdOptions = array(
			array(
				'value' => 0,
				'name'  => 'Reference Code'
			),
			array(
				'value' => 1,
				'name'  => 'EAN-13 or JAN barcode'
			),
			array(
				'value' => 2,
				'name'  => 'UPC barcode'
			),
		);

		$this->addSelectField( 'Product ID', 'map_id', $productIdOptions, true, $this->moduleInstance->l('Select the product reference group you are using in your store') );

		$productManufacturerOptions = array(
			array(
				'value' => 0,
				'name'  => 'Product Manufacturer'
			),
			array(
				'value' => 1,
				'name'  => 'Product Supplier'
			),
		);

		$this->addSelectField( 'Product Manufacturer', 'map_manufacturer', $productManufacturerOptions, true, $this->moduleInstance->l('Select the field you are using to specify the manufacturer') );

		$productLinkOptions = array(
			array(
				'value' => 0,
				'name'  => 'Use Product Link'
			),
		);

		$this->addSelectField( 'Product Link', 'map_link', $productLinkOptions, true, $this->moduleInstance->l('URL that leads to product. For upcoming features') );

		$productImageLinkOptions = array(
			array(
				'value' => 0,
				'name'  => 'Cover Image'
			),
			array(
				'value' => 1,
				'name'  => 'Random Image'
			),
		);

		$this->addSelectField( 'Product Image', 'map_image', $productImageLinkOptions, true, $this->moduleInstance->l('Choose if you want to use cover image or some random image from product\'s gallery') );

		$productCategoriesOptions = array(
			array(
				'value' => 0,
				'name'  => 'Categories'
			),
			array(
				'value' => 1,
				'name'  => 'Tags'
			),
		);

		$this->addSelectField( 'Product Categories', 'map_category', $productCategoriesOptions, true, $this->moduleInstance->l('Choose product tags if and only if no categories are set and instead product tags are in use') );

		$productPriceOptions = array(
			array(
				'value' => 0,
				'name'  => 'Retail price with tax'
			),
			array(
				'value' => 1,
				'name'  => 'Pre-tax retail price'
			),
			array(
				'value' => 2,
				'name'  => 'Pre-tax wholesale price'
			),
		);

		$this->addSelectField( 'Product Prices', 'map_price_with_vat', $productPriceOptions, true, $this->moduleInstance->l('s option specify the product price that will be used in XML. This should be left to "Retail price with tax"') );

		$productMPNOptions = array(
			array(
				'value' => 0,
				'name'  => 'Reference Code'
			),
			array(
				'value' => 1,
				'name'  => 'EAN-13 or JAN barcode'
			),
			array(
				'value' => 2,
				'name'  => 'UPC barcode'
			),
			array(
				'value' => 3,
				'name'  => 'Supplier Reference'
			),
		);

		$this->addSelectField( 'Product Manufacturer Reference Code', 'map_mpn', $productMPNOptions, true, $this->moduleInstance->l('This option should reflect product\' manufacturer SKU') );

		$productISBNOptions = array(
			array(
				'value' => 0,
				'name'  => 'Reference Code'
			),
			array(
				'value' => 1,
				'name'  => 'EAN-13 or JAN barcode'
			),
			array(
				'value' => 2,
				'name'  => 'UPC barcode'
			),
			array(
				'value' => 3,
				'name'  => 'Supplier Reference'
			),
		);

		$this->addSelectField( 'Product ISBN', 'map_isbn', $productISBNOptions, true, $this->moduleInstance->l('This field will be used if you sell books in your store, to specify the ISBN of the book') );

		// Multiselect from attribute groups
		$default_lang        = (int) \Configuration::get( 'PS_LANG_DEFAULT' );
		$productSizesOptions = array();
		$productColorOptions = array();
		$attributes          = \AttributeGroup::getAttributesGroups( $default_lang );

		foreach ( $attributes as $attribute ) {
			if ( $attribute['is_color_group'] ) {
				$productColorOptions[] = array(
					'value' => $attribute['id_attribute_group'],
					'name'  => $attribute['name'],
				);
			} else {
				$productSizesOptions[] = array(
					'value' => $attribute['id_attribute_group'],
					'name'  => $attribute['name'],
				);
			}
		}

		$this->addMultiSelectField( 'Size Attributes', 'map_size', $productSizesOptions, true, $this->moduleInstance->l('Choose the attributes that you use to specify product sizes. This field is used only if Fashion Store option is enabled') )
		     ->addMultiSelectField( 'Color Attributes', 'map_color', $productColorOptions, true, $this->moduleInstance->l('Choose the attributes that you use to specify product colors. This field is used only if Fashion Store option is enabled') );
	}
}