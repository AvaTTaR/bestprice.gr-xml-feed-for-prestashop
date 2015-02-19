<?php
/**
 * Project: ecosupplies
 * File: Options.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/2/2015
 * Time: 10:06 πμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace BestPrice;


class Options extends \XDaRk_v141110\Options{
	public $xmlIntervalOptions = array( 'daily' );

	/**
	 *
	 *
	 * @param $defaults
	 * @param $validators
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150219
	 */
	protected function setUp( $defaults, $validators ) {
		/***********************************************
		 * Actual options
		 ***********************************************/
		$options = array(
			/*********************
			 * XML File relative
			 ********************/
			// Internal, indicates XML generation progress
			'xml.progress'        => 0,
			// File location
			'xml_location'        => '',
			// File name
			'xml_fileName'        => 'bestprice.xml',
			// Generation interval
			'xml_interval'        => 'daily',
			/*********************
			 * Products relative
			 ********************/
			// Availability when products in stock
			'avail_inStock'       => '1-3 ημέρες',
			// Availability when products out stock
			'avail_outOfStock'    => '',
			// Availability when products out stock and backorders are allowed
			'avail_backorders'    => '',
			// Include disabled products
			'include_disabled'    => '0',
			/*********************
			 * Custom fields
			 ********************/
			'map_id'              => '0',
			'map_name'            => '0',
			'map_name_append_sku' => '1',
			'map_link'            => '0',
			'map_image'           => '0',
			'map_category'        => '0',
			'map_price_with_vat'  => '0',
			'map_manufacturer'    => '0',
			'map_mpn'             => '0',
			'map_isbn'            => '0',
			'map_size'            => array(),
			'map_color'           => array(),
			/********************
			 * Fashion store, book store
			 ********************/
			'is_fashion_store'    => '0',
			'is_book_store'       => '0',
			/********************
			 * General options
			 ********************/
			'last_created'        => '0',
			'request_var'         => 'bestprice',
			'request_var_value'   => '',
		);

		/***********************************************
		 * Validators
		 ***********************************************/
		$moduleValidators = array(
			/*********************
			 * XML File relative
			 ********************/
			// Internal, indicates XML generation progress
			'xml.progress'        => array( 'string:numeric >=' => 0, 'string:numeric <=' => 100 ),
			// File location
			'xml_location'        => array( 'string:!empty' ),
			// File name
			'xml_fileName'        => array( 'string:!empty' ),
			// Generation interval
			'xml_interval'        => array( 'string:in_array' => $this->xmlIntervalOptions ),
			/*********************
			 * Products relative
			 ********************/
			// Availability when products in stock
			'avail_inStock'       => array('string:!empty'),
			// Availability when products out stock
			'avail_outOfStock'    => array('string'),
			// Availability when products out stock and backorders are allowed
			'avail_backorders'    => array('string'),
			// Include disabled products
			'include_disabled'    => array(
				'string:numeric >=' => 0,
				'string:numeric <=' => 1
			),
			/*********************
			 * Custom fields
			 ********************/
			'map_id'              => array( 'string:numeric >=' => 0 ),
			'map_name'            => array( 'string:numeric >=' => 0 ),
			'map_name_append_sku' => array( 'string:numeric >=' => 0, 'string:numeric <=' => 1 ),
			'map_link'            => array( 'string:numeric >=' => 0 ),
			'map_image'           => array( 'string:numeric >=' => 0 ),
			'map_category'        => array( 'string:numeric >=' => 0 ),
			'map_price_with_vat'  => array( 'string:numeric >=' => 0 ),
			'map_manufacturer'    => array( 'string:numeric >=' => 0 ),
			'map_mpn'             => array( 'string:numeric >=' => 0 ),
			'map_isbn'            => array( 'string:numeric >=' => 0 ),
			'map_size'            => array( 'array' ),
			'map_color'           => array( 'array' ),
			/***********************************************
			 * Fashion store
			 ***********************************************/
			'is_fashion_store'    => array( 'string:numeric >=' => 0, 'string:numeric <=' => 1 ),
			'is_book_store'       => array( 'string:numeric >=' => 0, 'string:numeric <=' => 1 ),
			'last_created'        => array( 'string:numeric' ),
			'request_var'         => array( 'string:!empty' ),
			'request_var_value'   => array( 'string:!empty' ),
		);
		parent::setUp( array_merge( $defaults, $options ), array_merge( $validators, $moduleValidators ) );
	}
}