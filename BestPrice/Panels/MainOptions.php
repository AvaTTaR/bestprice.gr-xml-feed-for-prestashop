<?php
/**
 * Project: ecosupplies
 * File: MainOptions.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/2/2015
 * Time: 10:01 πμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace BestPrice\Panels;


use XDaRk_v141110\Panels\Panel;

class MainOptions extends Panel {
	protected $tab = 0;
	protected $type = 'main';
	protected $title = 'BestPrice.gr Main Options';
	protected $image = false; // TODO set a default image
	protected $input = array();
	protected $submit = array(
		'title' => 'Save',
		'class' => 'button pull-right'
	);

	/**
	 * @param \Module $moduleInstance
	 */
	public function __construct( $moduleInstance ) {
		parent::__construct( $moduleInstance );

		$this->addTextField( $this->l('XML Generate Request Variable Name'), 'request_var' )
		     ->addTextField( $this->l('XML Generate Request Variable Value'), 'request_var_value' )
		     ->addTextField( $this->l('XML file path'), 'xml_location', true, $this->l( 'File path relative to your PrestaShop install folder. eg "upload" is the PrestaShop upload dir' ) )
		     ->addTextField( $this->l('XML file name'), 'xml_fileName', true, $this->l( 'File name. eg "bestprice.xml"' ) )
		     ->addTextField( $this->l('Product availability when in stock'), 'avail_inStock', true, $this->l( 'This the product availability when this is in stock. It should match store availability string' ) )
		     ->addTextField( $this->l('Product availability when out of stock'), 'avail_outOfStock', false, $this->l( 'This the product availability when this is out stock. It should reflect store availability so default is empty' ) )
		     ->addTextField( $this->l('Product availability when out of stock and back-orders are allowed'), 'avail_backorders', false, $this->l( 'This the product availability when this is out of stock and back-orders are allowed. Default is empty' ) );

		$this
			->addYesNoField( $this->l('Append Product SKU to Product Name'), 'map_name_append_sku', true, $this->l( 'If this option is enabled then the product SKU will be appended to Product Name' ) )
			->addYesNoField( $this->l('Include disabled products'), 'include_disabled', true, $this->l( 'Set to yes if you want to include disabled products in XML file' ) )
			->addYesNoField( $this->l('Fashion store'), 'is_fashion_store', true, $this->l( 'Your store contains fashion items, eg shoes, clothes etc' ) )
			->addYesNoField( $this->l('Bookstore'), 'is_book_store', true, $this->l( 'Enable this if you are selling books' ) );
	}
}