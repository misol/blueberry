<?php
/* Copyright (C) Min-Soo Kim <misol@snu.ac.kr> */

/**
 * @class  blueberryModel
 * @author Min-Soo Kim <misol@snu.ac.kr>
 * @brief  Blueberry module  Model class
 **/
class blueberryModel extends module
{
	/**
	 * @brief initialization
	 **/
	function init()
	{
	}
	/**
	 * Import Data
	 * @param int $data_srl
	 * @param bool $is_admin
	 * @return documentItem
	 */
	public static function getData($data_srl = 0)
	{
		if(!$data_srl)
		{
			return new blueberryItem();
		}
		static $data = Array();
		
		if(isset($data[$data_srl])) return $data[$data_srl];
		
		$oData = new blueberryItem($data_srl, true);
		
		if(!$oData->isExists())
		{
			$data[$data_srl] = $oData;
		}
		
		return $oData;
	}
}