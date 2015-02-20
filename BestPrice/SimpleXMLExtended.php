<?php
/**
 * Project: ecosupplies
 * File: SimpleXMLExtended.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/2/2015
 * Time: 10:12 πμ
 * Since: 150219
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace BestPrice;


/**
 * Class SimpleXMLExtended extends SimpleXMLElement so CDATA con be added without encoding
 */
class SimpleXMLExtended extends \SimpleXMLElement {
	/**
	 * @param $cdata_text
	 *
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since 150219
	 */
	public function addCData($cdata_text) {
		$node = dom_import_simplexml($this);
		$no   = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cdata_text));
	}
}