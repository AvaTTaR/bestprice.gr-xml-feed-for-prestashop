<?php
/**
 * Project: bestpricexmlfeed
 * File: BestPrice.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/2/2015
 * Time: 10:14 πμ
 * Since: 150219
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace BestPrice;


use XDaRk_v141110\Core;

/**
 * Class BestPrice
 * @package BestPrice
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since 150219
 */
class BestPrice extends Core {
	/**
	 * @var int
	 */
	private $defaultLang;

	/**
	 * @param \Module $moduleInstance
	 */
	public function __construct( \Module &$moduleInstance ) {
		parent::__construct( $moduleInstance );
		$this->defaultLang = \Configuration::get( 'PS_LANG_DEFAULT' );
	}

	/**
	 * @return bool|int|void
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function generateXMLFile() {
		$productsArray = $this->createProductsArray();
		if ( ! $this->XML->parseArray( $productsArray ) ) {
			return false;
		}

		return count( $productsArray );
	}

	/**
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function printXMLFile() {
		$interval         = $this->getGenerationInterval();
		$xmlCreation      = $this->XML->getFileInfo();
		$createdTime      = strtotime( $xmlCreation['File Creation Datetime'] );
		$nextCreationTime = $interval + $createdTime;
		$time             = time();
		if ( $time > $nextCreationTime ) {
			$this->generateXMLFile();
		}

		$this->XML->printXML();
		exit( 0 );
	}

	/**
	 * @return array
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function createProductsArray() {
		$products = \Product::getProducts( $this->defaultLang, 0, 0, 'id_product', 'ASC', false, $this->Options->getValue( 'include_disabled' ) );

		$backOrdersAvailString = $this->Options->getValue( 'avail_backorders' );
		$outOfStockAvailString = $this->Options->getValue( 'avail_outOfStock' );
		$backOrdersInclude     = $this->String->is_not_empty( $backOrdersAvailString );
		$outOfStockInclude     = $this->String->is_not_empty( $outOfStockAvailString );

		foreach ( (array) $products as $key => $product ) {
			$p = new \Product( $product['id_product'] );

			// TODO Check for product avail etc

			$hasStock = $p->getRealQuantity( $p->id ) > 0; // TODO Is this a convenient way?

			if ( $p->getType() != 0
			     || $p->visibility == 'none'
			     || $p->available_for_order == 0
			) {
				unset( $products[ $key ] );
				// TODO Log skipped product
				continue;
			}

			if ( ! $hasStock ) {
				// TODO backOrdersAllowed not working as expected
				if ( $p->getRealQuantity( $p->id ) == 0 && ! $outOfStockInclude ) {
					unset( $products[ $key ] );
					// TODO Log skipped product
					continue;
				} else if ( $p->getRealQuantity( $p->id ) < 0 && ( ! $backOrdersInclude || ! $this->backOrdersAllowed( $p ) ) ) { // quantity < 0
					unset( $products[ $key ] );
					// TODO Log skipped product
					continue;
				}
			}

			$pushArray = $this->getProductArray( $p );

			if ( ! empty( $pushArray ) ) {
				$products[ $key ] = $pushArray;
			} else {
				// TODO Log skipped product
				unset( $products[ $key ] );
			}
		}

		return $products;
	}

	/**
	 * @param \Product $product
	 *
	 * @return array
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductArray( \Product &$product ) {
		$out = array();

		$out['productId']  = $this->getProductId( $product );
		$out['title']      = $this->getProductName( $product );
		$out['productURL'] = $this->getProductLink( $product );
		$out['imageURL']   = $this->getProductImageLink( $product );

		/***********************************************
		 * Prices
		 ***********************************************/
		$price     = $this->getProductPrice( $product );
		$salePrice = $this->getProductPrice( $product, 1 );
		if ( $salePrice > 0 && $salePrice < $price ) {
			$out['price']    = $this->formatPrice( $salePrice );
			$out['oldPrice'] = $this->formatPrice( $price );
		} else {
			$out['price'] = $this->formatPrice( $price );
		}

		$out['categoryID']   = $this->getProductCategories( $product, true );
		$out['categoryPath'] = $this->getProductCategories( $product, false );
		$out['brand']        = $this->getProductManufacturer( $product );
		$out['stock']        = $this->isInStock( $product );
		$out['availability'] = $this->getAvailabilityString( $product );
		$out['ΕΑΝ']          = $this->getProductMPN( $product );

		if ( count( (array) $this->Options->getValue( 'map_size' ) ) ) {
			$out['size'] = $this->getProductSizes( $product );
		}

		if ( count( (array) $this->Options->getValue( 'map_color' ) ) ) {
			$out['color'] = $this->getProductColors( $product );
		}

		return $out;
	}

	/**
	 * @param \Product $product
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductColors( \Product &$product ) {
		$colorList = \Product::getAttributesColorList( array( $product->id ) );

		if ( ! $colorList || empty( $colorList ) || ! isset( $colorList[ $product->id ] ) || empty( $colorList[ $product->id ] ) ) {
			return '';
		}

		$colors = array();
		foreach ( $colorList[ $product->id ] as $k => $color ) {
			if ( (int) $product->isColorUnavailable( $color['id_attribute'], \Context::getContext()->shop->id ) === $color['id_product_attribute'] && ! $this->backOrdersAllowed( $product ) ) {
				continue;
			}

			array_push( $colors, $color['name'] );
		}

		return implode( ', ', $colors );
	}

	/**
	 * @param \Product $product
	 *
	 * @return null|string
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductSizes( \Product &$product ) {
		$mapSizes = $this->Options->getValue( 'map_size' );

		if ( empty( $mapSizes ) ) {
			return null;
		}

		$sizes = array();
		foreach ( $product->getAttributeCombinations( $this->defaultLang ) as $key => $comp ) {
			if (
				$comp['is_color_group']
				|| ! in_array( $comp['id_attribute'], $mapSizes )
				|| ( $comp['quantity'] < 1 && ! $this->backOrdersAllowed( $product ) )
			) {
				continue;
			}

			$size = $this->sanitizeVariationString( $comp['attribute_name'] );
			if ( $this->isValidSizeString( $size ) ) {
				array_push( $sizes, $size );
			}
		}

		return implode( ', ', array_unique( $sizes ) );
	}

	/**
	 * @param $string
	 *
	 * @return mixed|string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function sanitizeVariationString( $string ) {
		$string = preg_replace( "/[^A-Za-z0-9 ]/", '.', strip_tags( trim( $string ) ) );
		$string = strtoupper( $string );

		return $string;
	}

	/**
	 * @param \Product $product
	 *
	 * @return mixed
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductManufacturer( \Product &$product ) {
		$option = $this->Options->getValue( 'map_manufacturer' );

		return $option == 0 ? $product->getWsManufacturerName() : \Supplier::getNameById( $product->id_supplier );
	}

	/**
	 * @param \Product $product
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function isInStock( \Product &$product ) {
		return ( $product->checkQty( 1 ) || $this->backOrdersAllowed( $product ) ) ? 'Y' : 'N';
	}

	/**
	 * !IMPORTANT! For now it always returns regular price
	 *
	 * @param \Product $product
	 * @param int $option 1: sale price, 2 tax excluded price, any other value regular price tax included. Default is last regular price tax included
	 *
	 * @return float
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductPrice( \Product &$product, $option = 0 ) {
		$option = 0; // TODO Remove this when it will implemented

		switch ( $option ) {
			case 1:
				$price = $product->getPrice( true, null, 2 );
				break;
			case 2:
				$price = $product->getPrice( false, null, 2 );
				break;
			default:
				$price = $product->getPrice( true, null, 2 );
				break;
		}

		return $price;
	}

	protected function formatPrice( $price ) {
		return number_format( floatval( $price ), 2, ',', '.' );
	}

	/**
	 * @param \Product $product
	 * @param bool $ids
	 *
	 * @return string
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductCategories( \Product &$product, $ids = false ) {
		$maxDepth = 3; // TODO Implement in options or remove
		$categories = array();
		if ( $this->Options->getValue( 'map_category' ) == 1 ) {
			$info = \Tag::getProductTags( $product->id );
			if ( $info && ! isset( $info[ $this->defaultLang ] ) ) {
				$categories = (array) $info[ $this->defaultLang ];
			}
		} else {
			$defaultCat = $product->getDefaultCategory();
			if($ids){
				return $defaultCat;
			}
			$category = new \Category($defaultCat);

			do{
				array_push( $categories, $ids ? $category->id : $category->name[$this->defaultLang] );
				$category = new \Category($category->id_parent);
			}while($category->id_parent && !$category->is_root_category);

			$categories = array_reverse($categories);
		}


		return is_array( $categories ) ? implode( $ids ? ' - ' : '->',  $categories)  : $categories;
	}

	/**
	 * @param \Product $product
	 *
	 * @return string
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductImageLink( \Product &$product ) {
		$link = new \Link();

		$imageLink = null;
		if ( $this->Options->getValue( 'map_image' ) == 1 ) {
			$images = $product->getImages( $this->defaultLang );
			if ( ! empty( $images ) ) {
				shuffle( $images );
				$imageLink = $link->getImageLink( $product->link_rewrite, end( $images )['id_image'] );
			}
		} else {
			$coverImage = \Image::getCover( $product->id );
			if ( $coverImage && ! empty( $coverImage ) && isset( $coverImage['id_image'] ) ) {
				$imageLink = $link->getImageLink( $product->link_rewrite, $coverImage['id_image'] );
			}
		}

		return empty( $imageLink ) ? '' : urldecode( $this->addHttp( $imageLink ) );
	}

	/**
	 * @param \Product $product
	 *
	 * @return string
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductId( \Product &$product ) {
		$option = $this->Options->getValue( 'map_id' );

		switch ( $option ) {
			case 1:
				$id = $product->ean13;
				break;
			case 2:
				$id = $product->upc;
				break;
			default:
				$id = $product->reference;
				break;
		}

		return $id;
	}

	/**
	 * @param \Product $product
	 *
	 * @return string
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductMPN( \Product &$product ) {
		$option = $this->Options->getValue( 'map_mpn' );

		switch ( $option ) {
			case 1:
				$id = $product->ean13;
				break;
			case 2:
				$id = $product->upc;
				break;
			case 3:
				$id = $product->supplier_reference;
				break;
			default:
				$id = $product->reference;
				break;
		}

		return $id;
	}

	/**
	 * @param \Product $product
	 *
	 * @return string
	 * @throws \PrestaShopException
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductLink( \Product &$product ) {
		$link = new \Link();

		$pLink = $link->getProductLink( $product );

		return urldecode( $this->addHttp( $pLink ) );
	}

	/**
	 * @param \Product $product
	 *
	 * @return string
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getProductName( \Product &$product ) {
		$name = is_array( $product->name ) && isset( $product->name[ $this->defaultLang ] ) ? $product->name[ $this->defaultLang ] : ( is_string( $product->name ) ? $product->name : 0 );

		return $name . ' ' . ( $this->Options->getValue( 'map_name_append_sku' ) ? $this->getProductId( $product ) : '' );
	}

	/**
	 * @param $url
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function addHttp( $url ) {
		if ( ! preg_match( "~^(?:f|ht)tps?://~i", $url ) ) {
			$url = "http://" . $url;
		}

		return $url;
	}

	/**
	 * @param \Product $product
	 *
	 * @return bool
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getAvailabilityString( \Product &$product ) {
		$hasStock = $product->getRealQuantity( $product->id ) > 0;
		$out      = false;
		// If product is in stock
		if ( $hasStock ) {
			$out = $this->Options->getValue( 'avail_inStock' );
		} elseif ( $this->backOrdersAllowed( $product ) ) {
			$backOrdersString = $this->Options->getValue( 'avail_backorders' );
			if ( $this->String->is_not_empty( $backOrdersString ) ) {
				$out = $backOrdersString;
			}
		} else {
			$outOfStockString = $this->Options->getValue( 'avail_outOfStock' );
			if ( $this->String->is_not_empty( $outOfStockString ) ) {
				$out = $outOfStockString;
			}
		}

		return $out;
	}

	/**
	 * @param $string
	 *
	 * @return mixed|string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function formatSizeColorStrings( $string ) {
		if ( is_array( $string ) ) {
			array_walk( $string, function ( $item, $key ) {
				return $this->formatSizeColorStrings( $item );
			} );

			return implode( ',', $string );
		}

		$patterns        = array();
		$patterns[0]     = '/\|/';
		$patterns[1]     = '/\s+/';
		$replacements    = array();
		$replacements[2] = ',';
		$replacements[1] = '';

		return preg_replace( $patterns, $replacements, $string );
	}

	/**
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function debug() {
		echo "<strong>not real mem usage: </strong>" . ( memory_get_peak_usage( false ) / 1024 / 1024 ) . " MiB<br>";
		echo "<strong>real mem usage: </strong>" . ( memory_get_peak_usage( true ) / 1024 / 1024 ) . " MiB<br>";
		$sTime     = microtime( true );
		$prodArray = $this->createProductsArray();
		echo "<strong>time: </strong>" . ( microtime( true ) - $sTime ) . " sec<br><br>";
		var_dump( $prodArray );
		die;
	}

	/**
	 * @param \Product $product
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function backOrdersAllowed( \Product $product ) {
		return \Product::isAvailableWhenOutOfStock( $product->out_of_stock ) == 1;
	}

	/**
	 * @param $string
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function isValidSizeString( $string ) {
		if ( is_numeric( $string ) ) {
			return true;
		}

		$validStrings = array(
			'XXS',
			'XS',
			'S',
			'M',
			'L',
			'XL',
			'XXL',
			'XXXL',
			'Extra Small',
			'Small',
			'Medium',
			'Large',
			'Extra Large'
		);

		return in_array( $string, $validStrings );
	}

	/**
	 * @return int
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function getGenerationInterval() {
		return 86400;
	}
}