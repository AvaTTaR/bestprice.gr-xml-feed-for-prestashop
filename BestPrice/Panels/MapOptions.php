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
				'name'  => $this->l('Reference Code')
			),
			array(
				'value' => 1,
				'name'  => $this->l('EAN-13 or JAN barcode')
			),
			array(
				'value' => 2,
				'name'  => $this->l('UPC barcode')
			),
		);

		$this->addSelectField( $this->l('Product ID'), 'map_id', $productIdOptions, true, $this->l('Select the product reference group you are using in your store') );

		$productManufacturerOptions = array(
			array(
				'value' => 0,
				'name'  => $this->l('Product Manufacturer')
			),
			array(
				'value' => 1,
				'name'  => $this->l('Product Supplier')
			),
		);

		$this->addSelectField( $this->l('Product Manufacturer'), 'map_manufacturer', $productManufacturerOptions, true, $this->l('Select the field you are using to specify the manufacturer') );

		$productLinkOptions = array(
			array(
				'value' => 0,
				'name'  => $this->l('Use Product Link')
			),
		);

		$this->addSelectField( $this->l('Product Link'), 'map_link', $productLinkOptions, true, $this->l('URL that leads to product. For upcoming features') );

		$productImageLinkOptions = array(
			array(
				'value' => 0,
				'name'  => $this->l('Cover Image')
			),
			array(
				'value' => 1,
				'name'  => $this->l('Random Image')
			),
		);

		$this->addSelectField( 'Product Image', 'map_image', $productImageLinkOptions, true, $this->l('Choose if you want to use cover image or some random image from product\'s gallery') );

		$productCategoriesOptions = array(
			array(
				'value' => 0,
				'name'  => $this->l('Categories')
			),
			array(
				'value' => 1,
				'name'  => $this->l('Tags')
			),
		);

		$this->addSelectField( $this->l('Product Categories'), 'map_category', $productCategoriesOptions, true, $this->l('Choose product tags if and only if no categories are set and instead product tags are in use') );

		$productPriceOptions = array(
			array(
				'value' => 0,
				'name'  => $this->l('Retail price with tax')
			),
			array(
				'value' => 1,
				'name'  => $this->l('Pre-tax retail price')
			),
			array(
				'value' => 2,
				'name'  => $this->l('Pre-tax wholesale price')
			),
		);

		$this->addSelectField( $this->l('Product Prices'), 'map_price_with_vat', $productPriceOptions, true, $this->l('This option specify the product price that will be used in XML. This should be left to "Retail price with tax"') );

		$productMPNOptions = array(
			array(
				'value' => 0,
				'name'  => $this->l('Reference Code')
			),
			array(
				'value' => 1,
				'name'  => $this->l('EAN-13 or JAN barcode')
			),
			array(
				'value' => 2,
				'name'  => $this->l('UPC barcode')
			),
			array(
				'value' => 3,
				'name'  => $this->l('Supplier Reference')
			),
		);

		$this->addSelectField( 'Product Manufacturer Reference Code', 'map_mpn', $productMPNOptions, true, $this->l('This option should reflect product\' manufacturer SKU') );

		$productISBNOptions = array(
			array(
				'value' => 0,
				'name'  => $this->l('Reference Code')
			),
			array(
				'value' => 1,
				'name'  => $this->l('EAN-13 or JAN barcode')
			),
			array(
				'value' => 2,
				'name'  => $this->l('UPC barcode')
			),
			array(
				'value' => 3,
				'name'  => $this->l('Supplier Reference')
			),
		);

		$this->addSelectField( $this->l('Product ISBN'), 'map_isbn', $productISBNOptions, true, $this->l('This field will be used if you sell books in your store, to specify the ISBN of the book') );

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

		$this->addMultiSelectField( $this->l('Size Attributes'), 'map_size', $productSizesOptions, true, $this->l('Choose the attributes that you use to specify product sizes. This field is used only if Fashion Store option is enabled') )
		     ->addMultiSelectField( $this->l('Color Attributes'), 'map_color', $productColorOptions, true, $this->l('Choose the attributes that you use to specify product colors. This field is used only if Fashion Store option is enabled') );
	}
}