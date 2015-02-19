<?php
/**
 * Project: ecosupplies
 * File: Hooks.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 19/2/2015
 * Time: 10:05 πμ
 * Since: TODO ${VERSION}
 * Copyright: 2015 Panagiotis Vagenas
 */

namespace BestPrice;

/**
 * Class Hooks
 * @package BestPrice
 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since TODO ${VERSION}
 *
 * @property \BestPrice\BestPrice    Skroutz
 * @property \BestPrice\XML          XML
 */
class Hooks extends \XDaRk_v141110\Hooks{
	/**
	 * @param $p
	 *
	 * @throws \Exception
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since TODO ${VERSION}
	 */
	public function hookDisplayHeader( $p ) {
		$queryVars = $this->Vars->getQueryVars();
		if(isset($queryVars[$this->Options->getValue('request_var')]) && $queryVars[$this->Options->getValue('request_var')] === $this->Options->getValue('request_var_value')){
			$this->BestPrice->printXMLFile();
		}
	}
}