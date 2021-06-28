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
	
	
	/**
	 * Module_srl value, bringing the list of documents
	 * @param object $obj
	 * @return Object
	 */
	public static function getInVivoDataByMemberSrl($obj, $columnList = array())
	{
		$sort_check = self::_setSortIndex($obj, $load_extra_vars);
		$obj->sort_index = $sort_check->sort_index;
		$obj->columnList = $columnList;
		
		// Call trigger (before)
		// This trigger can be used to set an alternative output using a different search method
		unset($obj->use_alternate_output);
		$output = ModuleHandler::triggerCall('blueberry.getInVivoDataByMemberSrl', 'before', $obj);
		if($output instanceof BaseObject && !$output->toBool())
		{
			return $output;
		}
		
		/*
		// If an alternate output is set, use it instead of running the default queries
		if (isset($obj->use_alternate_output) && $obj->use_alternate_output instanceof BaseObject)
		{
			$output = $obj->use_alternate_output;
		}
		// execute query
		else
		{
			self::_setSearchOption($obj, $args, $query_id, $use_division);
			$output = executeQueryArray($query_id, $args, $args->columnList);
		}
		*/
		
			$output = executeQueryArray('blueberry.getInVivoDataByMemberSrl', $obj, $obj->columnList);
		
		// Return if no result or an error occurs
		if(!$output->toBool()) {
			return $output;
		}
		if(!$result = $output->data)
		{
			return array();
		}
		
		$output->data = array();
		foreach($result as $key => $attribute)
		{
			$oData = new blueberryItem();
			$oData->setAttribute($attribute, false);
			$output->data[$key] = $oData;
			unset($oData);
		}
		
		// Call trigger (after)
		// This trigger can be used to modify search results
		ModuleHandler::triggerCall('blueberry.getInVivoDataByMemberSrl', 'after', $output);
		return $output->data;
	}
	
	/**
	 * Setting sort index
	 * @param object $obj
	 * @param bool $load_extra_vars
	 * @return object
	 */
	public static function _setSortIndex($obj, $load_extra_vars = true)
	{
		$args = new stdClass;
		$args->sort_index = $obj->sort_index ?? null;
		$args->isExtraVars = false;
		
		// check it's default sort
		$default_sort = array('list_order', 'regdate', 'last_update', 'update_order', 'readed_count', 'voted_count', 'blamed_count', 'comment_count', 'title');
		if(in_array($args->sort_index, $default_sort))
		{
			return $args;
		}
		
		$args->sort_index = 'list_order';
		return $args;
	}
}