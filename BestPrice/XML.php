<?php
/**
 * Project: ecosupplies
 * File: XML.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/2/2015
 * Time: 10:13 πμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace BestPrice;


class XML extends \XDaRk_v141110\XML{
	/**
	 * @var array
	 */
	protected $bpcXMLFields = array(
		'productId',
		'title',
		'productURL',
		'imageURL',
		'price',
		'categoryID',
		'categoryPath',
		'brand',
		'ISBN',
		'size',
		'stock',
		'availability',
		'description',
		'oldPrice',
		'shipping',
		'color',
		'features',
		'EAN',
		'netprice',
		'isBundle',
	);
	/**
	 * @var array
	 */
	protected $bpcXMLFieldsLengths = array(
		'productId'    => 128,
		'title'        => 250,
		'productURL'   => 250,
		'imageURL'     => 250,
		'categoryID'   => 64,
		'categoryPath' => 250,
		'brand'        => 128,
		'size'         => 256,
		'stock'        => 1,
		'availability' => 64,
		'color'        => 128,
		'EAN'          => 128,
		'isBundle'     => 1,
	);
	/**
	 * @var array
	 */
	protected $bpcXMLRequiredFields = array(
		'productId',
		'title',
		'productURL',
		'imageURL',
		'price',
		'categoryID',
		'categoryPath',
		'brand',
		'ISBN',
		'size',
		'stock',
		'availability',
	);

	/**
	 * @var SimpleXMLExtended
	 */
	public $simpleXML = null;

	/**
	 * Absolute file path
	 * @var string
	 */
	public $fileLocation = '';

	public $createdAt = null;
	public $createdAtName = 'date';

	protected $rootElemName = 'store';
	protected $productsElemWrapper = 'products';
	protected $productElemName = 'product';

	public function __construct(\Module &$moduleInstance){
		parent::__construct($moduleInstance);
		$d = array();
		if ( ! (bool) $this->Options->getValue( 'is_fashion_store' ) ) {
			$d[] = 'size';
		}

		if ( ! (bool) $this->Options->getValue( 'is_book_store' ) ) {
			$d[] = 'ISBN';
		}
		$this->bpcXMLRequiredFields = array_diff($this->bpcXMLRequiredFields, $d);
	}

	/**
	 * @param array $array
	 *
	 * @return bool|mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function parseArray( Array $array ) {
		// init simple xml if is not initialized already
		if ( ! $this->simpleXML ) {
			$this->initSimpleXML();
		}

		// get products node
		$products = $this->simpleXML->children();

		// parse array
		foreach ( $array as $k => $v ) {
			$validated = $this->validateArrayKeys( $v );

			if ( empty( $validated ) ) {
				unset( $array[ $k ] );
			} else {
				/* @var SimpleXMLExtended $product */
				$product = $products->addChild( $this->productElemName );

				foreach ( $validated as $key => $value ) {
					$this->addChildNode( $key, $value, $product );
				}
			}
		}

		return ! empty( $array ) && $this->saveXML();
	}

	protected function addChildNode( $key, $value, \SimpleXMLElement $node ) {
		if ( is_array( $value ) ) {
			$n = $node->addChild( $key );
			foreach ( $value as $k => $v ) {
				$this->addChildNode( $k, $v, $n );
			}
		} else if ( $this->isValidXmlName( $value ) ) {
			$node->addChild( $key, $value );
		} else {
			if(!isset($node->$key)) $node->addChild($key);
			$node->$key->addCData( $value );
		}
	}

	/**
	 * @return $this
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function initSimpleXML() {
		$this->fileLocation = $this->getFileLocation();

		$this->simpleXML = new SimpleXMLExtended( '<' . $this->rootElemName . '></' . $this->rootElemName . '>' );
		$this->simpleXML->addChild( $this->productsElemWrapper );

		return $this;
	}

	/**
	 * @param array $array
	 *
	 * @return array
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function validateArrayKeys( Array $array ) {
		foreach ( $this->bpcXMLRequiredFields as $fieldName ) {
			if ( ! isset( $array[ $fieldName ] ) || empty( $array[ $fieldName ] ) ) {
				$fields = array();
				foreach ( $this->bpcXMLRequiredFields as $f ) {
					if ( ! isset( $array[ $f ] ) || empty( $array[ $f ] ) ) {
						array_push( $fields, $f );
					}
				}
				$name = isset( $array['title'] )
					? $array['title']
					: ( isset( $array['productId'] )
						? 'with id ' . $array['productId']
						: '' );
				// TODO Log skipped product

				return array();
			} else {
				$array[ $fieldName ] = $this->trimField( $array[ $fieldName ], $fieldName );
				if ( is_string( $array[ $fieldName ] ) && !is_numeric($array[ $fieldName ]) ) {
					$array[ $fieldName ] = mb_convert_encoding( $array[ $fieldName ], "UTF-8" );
				}
			}
		}

		foreach ( $array as $k => $v ) {
			if ( ! in_array( $k, $this->bpcXMLFields ) ) {
				unset( $array[ $k ] );
			}
		}

		return $array;
	}

	protected function isValidXmlName( $name ) {
		try {
			new \DOMElement( $name );

			return true;
		} catch ( \DOMException $e ) {
			return false;
		}
	}

	/**
	 * @param $value
	 * @param $fieldName
	 *
	 * @return bool|string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function trimField( $value, $fieldName ) {
		if ( ! isset( $this->bpcXMLFieldsLengths[ $fieldName ] ) ) {
			return $value;
		}

		if ( $this->bpcXMLFieldsLengths[ $fieldName ] === 0 ) {
			return $value;
		}

		return substr( trim((string) $value), 0, $this->bpcXMLFieldsLengths[ $fieldName ] );
	}


	/**
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function loadXML() {
		/**
		 * For now we write it from scratch EVERY TIME
		 */
		$this->fileLocation = $this->getFileLocation();

		@unlink( $this->fileLocation );

		return false;
	}

	/**
	 * @return bool|mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function saveXML() {
		$dir = dirname($this->fileLocation);
		if(!file_exists($dir)){
			mkdir($dir, 0755, true);
		}

		if ( $this->simpleXML && ! empty( $this->fileLocation ) && (is_writable( $this->fileLocation ) || is_writable($dir) ) ) {
			$this->simpleXML->addChild( $this->createdAtName, date( 'Y-m-d H:i' ) );
			return $this->simpleXML->asXML( $this->fileLocation ); // TODO Will this create the dir path?
		}

		return false;
	}

	/**
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function printXML() {
		if(headers_sent()) return;

		if ( ! ( $this->simpleXML instanceof SimpleXMLExtended )) {
			$fileLocation = $this->getFileLocation();
			if ( !$this->existsAndReadable( $fileLocation ) ) {
				die('EW:X:P');
			}
			$this->simpleXML = simplexml_load_file( $fileLocation );
		}

		header ("Content-Type:text/xml");

		echo $this->simpleXML->asXML();

		exit(0);
	}

	/**
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function getFileLocation() {
		$location =  trim($this->Options->getValue( 'xml_location' ), '\\/ ' );
		$fileName = $this->Options->getValue( 'xml_fileName' );

		return rtrim( _PS_ROOT_DIR_, '\\/' ) . '/' . (empty($location) ? '' : $location . '/' ) . rtrim(ltrim($fileName, '\\/'), '\\/');
	}

	/**
	 * @return array|null
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function getFileInfo() {
		$fileLocation = $this->getFileLocation();

		if ( $this->existsAndReadable( $fileLocation ) ) {
			$info = array();

			$sXML = simplexml_load_file( $fileLocation );
			$cratedAtName = $this->createdAtName;

			$info[ 'File Creation Datetime' ] = end( $sXML->$cratedAtName );
			$info['Products Count']       = $this->countProductsInFile( $sXML );
			$info['File Path']            = $fileLocation;
//			$info['File Url']             = $this->©url->to_wp_site_uri( str_replace( ABSPATH, '', $fileLocation ) ); TODO File URL
			$info['File Size']            = filesize( $fileLocation );

			return $info;
		} else {
			return null;
		}
	}

	/**
	 * @param $file
	 *
	 * @return int
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function countProductsInFile( $file ) {
		if ( $this->existsAndReadable( $file ) ) {
			$sXML = simplexml_load_file( $file );
		} elseif ( $file instanceof \SimpleXMLElement || $file instanceof SimpleXMLExtended ) {
			$sXML = &$file;
		} else {
			return 0;
		}

		if ( $sXML->getName() == $this->productsElemWrapper ) {
			return $sXML->count();
		}elseif ( $sXML->getName() == $this->rootElemName ) {
			return $sXML->children( )->children()->count();
		}

		return 0;
	}

	/**
	 * @param $file
	 *
	 * @return bool
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	protected function existsAndReadable( $file ) {
		return is_string( $file ) && file_exists( $file ) && is_readable( $file );
	}

	/**
	 * @param \SimpleXMLElement $xml
	 * @param $attribute
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150213
	 */
	public function attribute(\SimpleXMLElement $xml, $attribute)
	{
		foreach($xml->attributes() as $_attribute => $_value)
			if(strcasecmp($_attribute, $attribute) === 0)
				return (string)$_value;
		unset($_attribute, $_value);

		return '';
	}
}